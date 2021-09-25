<?php namespace lang;

/**
 * Handles command line quoting
 *
 * Composing a command line
 * ------------------------
 * Handled by the <tt>compose</tt> method.
 *
 * For Windows
 * ~~~~~~~~~~~
 * - Surround string with double quotes
 * - Replace double quotes inside with triple quotes (""")
 * 
 * For Un*x
 * ~~~~~~~~
 * - Surround string with single quotes
 * - As single quotes may not appear inside a string enclosed in
 *   single quotes, split it and add a single quoted escaped by
 *   a backslash. So: he said: 'Hello' will become the following:
 *   'he said: '\''Hello'\''
 *
 * @see      xp://lang.Process
 * @test     xp://net.xp_framework.unittest.core.CommandLineTest
 */
abstract class CommandLine extends Enum {
  public static $WINDOWS, $UNIX;
  private static $PATH;

  static function __static() {
    self::$WINDOWS= new class(0, 'WINDOWS') extends CommandLine {
      private static $EXT;

      static function __static() { }

      public function parse($cmd) {
        static $triple= '"""';
        $parts= [];
        $r= '';
        for ($i= 0, $s= strlen($cmd); $i < $s; $i++) {
          if (' ' === $cmd[$i]) {
            $parts[]= $r;
            $r= '';
          } else if ('"' === $cmd[$i]) {
            $q= $i+ 1;
            do {
              if (false === ($p= strpos($cmd, '"', $q))) {
                $q= $s;
                break;
              }
              $q= $p;
              if ($triple === substr($cmd, $q, 3)) {
                if (false === ($p= strpos($cmd, $triple, $q+ 3))) {
                  $q= $q+ 3;
                  continue;
                }
                $q= $p+ 3;
                continue;
              }
              break;
            } while ($q < $s);
            $r.= str_replace($triple, '"', substr($cmd, $i+ 1, $q- $i- 1));
            $i= $q;
          } else {
            $r.= $cmd[$i];
          }
        }
        $parts[]= $r;
        return $parts;
      }

      public function resolve($command) {
        if ('' === $command) return;

        $dot= strrpos($command, '.') > 0;
        if ('\\' === $command[0] || '/' === $command[0] || 2 === sscanf($command, '%c%[:]', $drive, $colon)) {
          foreach ($dot ? [''] : self::$EXT ?? self::$EXT= explode(';', getenv('PATHEXT')) as $ext) {
            $q= $command.$ext;
            is_file($q) && is_executable($q) && yield realpath($q);
          }
        } else {
          parent::$PATH ?? parent::$PATH= explode(';', getenv('PATH'));
          foreach (parent::$PATH as $path) {
            foreach ($dot ? [''] : self::$EXT ?? self::$EXT= explode(';', getenv('PATHEXT')) as $ext) {
              $q= $path.'\\'.$command.$ext;
              is_file($q) && is_executable($q) && yield realpath($q);
            }
          }
        }
      }

      protected function quote($arg) {
        $l= strlen($arg);
        if ($l && strcspn($arg, '" ') >= $l) return $arg;
        return '"'.str_replace('"', '"""', $arg).'"';
      }

      public function compose($command, $arguments= []) {
        $cmd= $this->quote($command);
        foreach ($arguments as $arg) {
          $cmd.= ' '.$this->quote($arg);
        }
        return $cmd;
      }
    };
    self::$UNIX= new class(1, 'UNIX') extends CommandLine {
      static function __static() { }

      public function parse($cmd) {
        $parts= [];
        $o= 0;
        $l= strlen($cmd);
        while ($o < $l) {
          $p= strcspn($cmd, ' ', $o);
          $option= substr($cmd, $o, $p);
          if (1 === substr_count($option, '"')) {
            $ql= $o+ $p;
            $qp= strpos($cmd, '"', $ql)+ 1;
            $option.= substr($cmd, $ql, $qp- $ql);
            $o= $qp+ 1;
          } else if (1 === substr_count($option, "'")) {
            $ql= $o+ $p;
            $qp= strpos($cmd, "'", $ql)+ 1;
            $option.= substr($cmd, $ql, $qp- $ql);
            $o= $qp+ 1;
          } else {
            $o+= $p+ 1;
          }
          if ('"' === $option[0] || "'" === $option[0]) $option= substr($option, 1, -1);
          $parts[]= $option;
        }
        return $parts;
      }

      public function resolve($command) {
        if ('' === $command) {
          // NOOP
        } else if ('/' === $command[0]) {
          is_file($command) && yield realpath($command);
        } else {
          foreach (parent::$PATH ?? parent::$PATH= explode(PATH_SEPARATOR, getenv('PATH')) as $path) {
            $q= $path.DIRECTORY_SEPARATOR.$command;
            is_file($q) && is_executable($q) && yield realpath($q);
          }
        }
      }

      protected function quote($arg) {
        $l= strlen($arg);
        if ($l && strcspn($arg, "&;`\'\"|*?~<>^()[]{}\$ ") >= $l) return $arg;
        return "'".str_replace("'", "'\\''", $arg)."'";
      }

      public function compose($command, $arguments= []) {
        $cmd= $this->quote($command);
        foreach ($arguments as $arg) {
          $cmd.= ' '.$this->quote($arg);
        }
        return $cmd;
      }
    };
  }
  
  /**
   * Returns the command line implementation for the given operating 
   * system.
   *
   * @param   string os operating system name, e.g. PHP_OS
   * @return  lang.CommandLine
   */
  public static function forName(string $os): self {
    if (0 === strncasecmp($os, 'Win', 3)) {
      return self::$WINDOWS;
    } else {
      return self::$UNIX;
    }
  }

  /**
   * Parse command line
   *
   * @param   string cmd
   * @return  string[] parts
   */
  public abstract function parse($cmd);

  /**
   * Resolve a command
   *
   * @param  string command
   * @return iterable
   */
  public abstract function resolve($command);
  
  /**
   * Build command line from a command and - optionally - arguments
   *
   * @param   string command
   * @param   string[] arguments default []
   * @return  string
   */
  public abstract function compose($command, $arguments= []);
}
