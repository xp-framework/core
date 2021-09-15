<?php namespace lang;

use io\{File, IOException};

/**
 * Process
 *
 * Example (get uptime information on a *NIX system)
 * ```php
 * $p= new Process('uptime');
 * $uptime= $p->out->readLine();
 * $p->close();
 *
 * var_dump($uptime);
 * ```
 *
 * @test  xp://net.xp_framework.unittest.core.ProcessResolveTest
 * @test  xp://net.xp_framework.unittest.core.ProcessTest
 * @see   xp://lang.Runtime#getExecutable
 * @see   php://proc_open
 */
class Process {
  public
    $in     = null,
    $out    = null,
    $err    = null,
    $exitv  = -1;
  
  private $handle;
  private $status= [];

  public static $DISABLED;

  static function __static() {
    self::$DISABLED= (bool)strstr(ini_get('disable_functions'), 'proc_open');
  }

  /**
   * Constructor
   *
   * @param   string command default NULL
   * @param   string[] arguments default []
   * @param   string cwd default NULL the working directory
   * @param   [:string] default NULL the environment
   * @throws  io.IOException in case the command could not be executed
   */
  public function __construct($command= null, $arguments= [], $cwd= null, $env= null) {
    static $spec= [
      0 => ['pipe', 'r'],  // stdin
      1 => ['pipe', 'w'],  // stdout
      2 => ['pipe', 'w']   // stderr
    ];

    // For `new self()` used in getProcessById()
    if (null === $command) return;

    // Verify
    if (self::$DISABLED) {
      throw new IOException('Process execution has been disabled');
    }

    // Check whether the given command is executable.
    $binary= self::resolve($command);
    if (!is_file($binary) || !is_executable($binary)) {
      throw new IOException('Command "'.$binary.'" is not an executable file');
    }

    // Open process
    $cmd= CommandLine::forName(PHP_OS)->compose($binary, $arguments);
    if (!is_resource($this->handle= proc_open($cmd, $spec, $pipes, $cwd, $env, ['bypass_shell' => true]))) {
      throw new IOException('Could not execute "'.$cmd.'"');
    }

    $this->status= proc_get_status($this->handle);
    $this->status['exe']= $binary;
    $this->status['arguments']= $arguments;
    $this->status['owner']= true;
    $this->status['running?']= function() {
      if (null === $this->handle) return false;
      return $this->status['running']= proc_get_status($this->handle)['running'];
    };
    $this->status['terminate!']= function($signal) {
      if (null === $this->handle) return false;
      proc_terminate($this->handle, $signal);
    };

    // Assign in, out and err members
    $this->in= new File($pipes[0]);
    $this->out= new File($pipes[1]);
    $this->err= new File($pipes[2]);
  }

  /**
   * Create a new instance of this process.
   *
   * @param   string[] arguments default []
   * @param   string cwd default NULL the working directory
   * @param   [:string] default NULL the environment
   * @return  self
   * @throws  io.IOException in case the command could not be executed
   */
  public function newInstance($arguments= [], $cwd= null, $env= null): self {
    return new self($this->status['exe'], $arguments, $cwd, $env);
  }

  /**
   * Resolve path for a command
   *
   * @param   string|string[] commands
   * @return  string executable
   * @throws  io.IOException in case the command could not be found or is not an executable
   */
  public static function resolve($commands): string {
    clearstatcache();

    // PATHEXT is in form ".{EXT}[;.{EXT}[;...]]"
    $extensions= array_merge([''], explode(PATH_SEPARATOR, getenv('PATHEXT')));
    $paths= explode(PATH_SEPARATOR, getenv('PATH'));

    foreach ((array)$commands as $command) {
  
      // Short-circuit this
      if ('' === $command) throw new IOException('Empty command not resolveable');
    
      // If the command is in fully qualified form and refers to a file
      // that does not exist (e.g. "C:\DoesNotExist.exe", "\DoesNotExist.com"
      // or /usr/bin/doesnotexist), do not attempt to search for it.
      if ((DIRECTORY_SEPARATOR === $command[0]) || ((strncasecmp(PHP_OS, 'Win', 3) === 0) && 
        strlen($command) > 1 && (':' === $command[1] || '/' === $command[0])
      )) {
        foreach ($extensions as $ext) {
          $q= $command.$ext;
          if (file_exists($q) && !is_dir($q)) return realpath($q);
        }
        continue;
      }

      // Check the PATH environment setting for possible locations of the 
      // executable if its name is not a fully qualified path name.
      foreach ($paths as $path) {
        foreach ($extensions as $ext) {
          $q= $path.DIRECTORY_SEPARATOR.$command.$ext;
          if (file_exists($q) && !is_dir($q)) return realpath($q);
        }
      }
    }

    throw new IOException('Could not find '.implode(', ', (array)$commands).' in path');
  }

  /**
   * Get a process by process ID
   *
   * @param   int pid process id
   * @param   string exe
   * @return  self
   * @throws  lang.IllegalStateException
   */
  public static function getProcessById($pid, $exe= null): self {
    $self= new self();
    $self->status= [
      'pid'       => $pid, 
      'running'   => true,
      'exe'       => $exe,
      'command'   => '',
      'arguments' => null,
      'owner'     => false,
      'running?'  => null
    ];

    // Determine executable and command line:
    // * On Windows, use Windows Management Instrumentation API - see
    //   http://en.wikipedia.org/wiki/Windows_Management_Instrumentation
    //   via com_dotnet, falling back to the "wmic" command line tool.
    //
    // * On systems with a /proc filesystem, use information from /proc/self
    //   See http://en.wikipedia.org/wiki/Procfs. Before relying on it, 
    //   also check that /proc is not just an empty directory; this assumes 
    //   that process 1 always exists - which usually is `init`.
    //
    // * Fall back to use "_" environment variable for the executable and
    //   /bin/ps to retrieve the command line (please note unfortunately any
    //   quote signs have been lost and it can thus be only used for display
    //   purposes)
    if (strncasecmp(PHP_OS, 'Win', 3) === 0) {
      try {
        if (class_exists(\Com::class)) {
          $c= new \Com('winmgmts://./root/cimv2');
          $p= $c->get('Win32_Process.Handle="'.$pid.'"');
          if (null === $exe) $self->status['exe']= $p->executablePath;
          $self->status['command']= $p->commandLine;
          $self->status['running?']= function() use($c, $pid) {
            $p= $c->execQuery('select * from Win32_Process where Handle="'.$pid.'"');
            foreach ($p as $result) {
              return true;
            }
            return false;
          };
        } else {
          exec('wmic process where handle='.$pid.' get ExecutablePath,CommandLine /format:list 2>&1', $out, $r);
          $p= [];
          foreach ($out as $line) {
            if (2 === sscanf($line, "%[^=]=%[^\r]", $key, $value)) {
              $p[$key]= $value;
            }
          }

          if (0 !== $r || !isset($p['ExecutablePath'])) {
            throw new IllegalStateException(implode(' ', $out));
          }

          if (null === $exe) $self->status['exe']= $p['ExecutablePath'];
          $self->status['command']= $p['CommandLine'];
          $self->status['running?']= function() use($pid) {
            exec('wmic process where handle='.$pid.' get Handle 2>NUL', $out);
            return in_array($pid, $out);
          };
        }
        $self->status['terminate!']= function($signal) use($pid) {
          exec('taskkill /F /T /PID '.$pid);
        };
      } catch (\Throwable $e) {
        throw new IllegalStateException('Cannot find executable: '.$e->getMessage());
      }
    } else if (is_dir('/proc/1')) {
      if (!file_exists($proc= '/proc/'.$pid)) {
        throw new IllegalStateException('Cannot find executable in /proc');
      }
      if (null === $exe) do {
        foreach (['/exe', '/file'] as $alt) {
          if (!file_exists($proc.$alt)) continue;
          $self->status['exe']= readlink($proc.$alt);
          break 2;
        }
        throw new IllegalStateException('Cannot find executable in '.$proc);
      } while (0);
      $self->status['command']= strtr(file_get_contents($proc.'/cmdline'), "\0", ' ');
      $self->status['running?']= function() use($pid) {
        return file_exists('/proc/'.$pid);
      };
      $self->status['terminate!']= function($signal) use($pid) {
        exec('kill -'.$signal.' '.$pid);
      };
    } else {
      try {
        if (null !== $exe) {
          // OK
        } else if ($_= getenv('_')) {
          $self->status['exe']= self::resolve($_);
        } else {
          throw new IllegalStateException('Cannot find executable');
        }
        $self->status['command']= exec('ps -ww -p '.$pid.' -ocommand 2>&1', $out, $exit);
        if (0 !== $exit) {
          throw new IllegalStateException('Cannot find executable: '.implode('', $out));
        }
      } catch (IOException $e) {
        throw new IllegalStateException($e->getMessage());
      }
      $self->status['running?']= function() use($pid) {
        exec('ps -p '.$pid, $out, $exit);
        return 0 === $exit;
      };
      $self->status['terminate!']= function($signal) use($pid) {
        exec('kill -'.$signal.' '.$pid);
      };
    }

    $self->in= $self->out= $self->err= null;
    return $self;
  }
  
  /** Get process ID */
  public function getProcessId(): int { return $this->status['pid']; }
  
  /** Get filename of executable */
  public function getFilename(): string { return $this->status['exe']; }

  /** Get command line */
  public function getCommandLine(): string { return $this->status['command']; }
  
  /**
   * Get command line arguments
   *
   * @return  string[]
   */
  public function getArguments() {
    if (null === $this->status['arguments']) {
      $parts= CommandLine::forName(PHP_OS)->parse($this->status['command']);
      $this->status['arguments']= array_slice($parts, 1);
    }
    return $this->status['arguments'];
  }
  
  /** Get error stream */
  public function getErrorStream(): File { return $this->err; }

  /** Get input stream */
  public function getInputStream(): File { return $this->in; }
  
  /** Get output stream */
  public function getOutputStream(): File { return $this->out; }
  
  /** Returns the exit value for the process */
  public function exitValue(): int { return $this->exitv; }

  /** Returns whether the process is running */
  public function running(): bool { return $this->status['running?'](); }
  
  /**
   * Close this process
   *
   * @return  int exit value of process
   * @throws  lang.IllegalStateException if process is not owned
   */
  public function close(): int {
    if (!$this->status['owner']) {
      throw new IllegalStateException('Cannot close not-owned process #'.$this->status['pid']);
    }
    if (null !== $this->handle) {
      $this->in->isOpen() && $this->in->close();
      $this->out->isOpen() && $this->out->close();
      $this->err->isOpen() && $this->err->close();
      $this->exitv= proc_close($this->handle);
      $this->handle= null;
    }
    
    // If the process wasn't running when we entered this method,
    // determine the exitcode from the previous proc_get_status()
    // call.
    if (!$this->status['running']) {
      $this->exitv= $this->status['exitcode'];
    }
    return $this->exitv;
  }

  /**
   * Kills the given process. Returns immediately, use `running()` to poll for whether
   * the process has exited.
   *
   * @param  int $signal
   * @return void
   */
  public function terminate($signal= 15) {
    $this->status['terminate!']($signal);
  }
}
