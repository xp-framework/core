<?php namespace lang;

/**
 * Represents the runtime - that is, the PHP binary executing the
 * current process.
 *
 * @test  xp://net.xp_framework.unittest.core.SystemExitTest
 * @test  xp://net.xp_framework.unittest.core.RuntimeTest
 */
class Runtime extends Object {
  protected static 
    $instance   = null;
    
  protected
    $executable = null,
    $startup    = null;
  
  static function __static() {
    self::$instance= new self();
  }
  
  /**
   * Retrieve the runtime instance
   *
   * @return  lang.Runtime
   */
  public static function getInstance() {
    return self::$instance;
  }
  
  /**
   * Loads a dynamic library.
   *
   * @see     php://dl
   * @param   string name
   * @return  bool TRUE if the library was loaded, FALSE if it was already loaded
   * @throws  lang.IllegalAccessException in case library loading is prohibited
   * @throws  lang.ElementNotFoundException in case the library does not exist
   * @throws  lang.RuntimeError in case dl() fails
   */
  public function loadLibrary($name) {
    if (extension_loaded($name)) return false;
  
    // dl() will fatal if any of these are set - prevent this
    if (!(bool)ini_get('enable_dl') || (bool)ini_get('safe_mode')) {
      throw new IllegalAccessException(sprintf(
        'Loading libraries not permitted by system configuration [enable_dl= %s, safe_mode= %s]',
        ini_get('enable_dl'),
        ini_get('safe_mode')
      ));
    }
    
    // Qualify filename
    $path= rtrim(realpath(ini_get('extension_dir')), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    $filename= $name.'.'.PHP_SHLIB_SUFFIX;
    
    // Try php_<name>.<ext>, <name>.<ext>      
    if (file_exists($lib= $path.'php_'.$filename)) {
      // E.g. php_sybase_ct.dll
    } else if (file_exists($lib= $path.$filename)) {
      // E.g. sybase_ct.so
    } else {
      throw new ElementNotFoundException('Cannot find library "'.$name.'" in "'.$path.'"');
    }
    
    // Found library, try to load it. dl() expects given argument to not contain
    // a path and will fail with "Temporary module name should contain only 
    // filename" if it does.
    if (!dl(basename($lib))) {
      throw new RuntimeError('dl() failed for '.$lib);
    }
    return true;
  }

  /**
   * Returns the total amount of memory available to the runtime. If there
   * is no limit zero will be returned.
   *
   * @return  int bytes
   */
  public function memoryLimit() {
    return (int)ini_get('memory_limit');
  }

  /**
   * Returns the amount of memory currently allocated by the runtime
   *
   * @see     php://memory_get_peak_usage
   * @return  int bytes
   */
  public function memoryUsage() {
    return memory_get_usage();
  }

  /**
   * Returns the peak of of memory that has been allocated by the
   * runtime up until now.
   *
   * @see     php://memory_get_usage
   * @return  int bytes
   */
  public function peakMemoryUsage() {
    return memory_get_peak_usage();
  }

  /**
   * Check whether a given extension is available
   *
   * @see     php://extension_loaded
   * @param   string name
   * @return  bool
   */
  public function extensionAvailable($name) {
    return extension_loaded($name);
  }

  /**
   * Register a shutdown hook - a piece of code that will be run before
   * the runtime shuts down (e.g. with exit).
   *
   * @see     php://register_shutdown_function
   * @param   lang.Runnable r
   * @return  lang.Runnable the given runnable
   */
  public function addShutdownHook(Runnable $r) {
    register_shutdown_function([$r, 'run']);
    return $r;
  }
  
  /**
   * Stops execution by raising a SystemExit
   *
   * @see     xp://lang.SystemExit
   * @param   int code default 0
   * @param   string message default NULL
   */
  public static function halt($code= 0, $message= null) {
    throw new SystemExit($code, $message);
  }

  /**
   * Parse command line, stopping at first argument without "-"
   * or at "--" (php [options] -- [args...])
   *
   * @param   string[] arguments
   * @return  [:var]
   * @throws  lang.FormatException in case an unrecognized argument is encountered
   */
  public static function parseArguments($arguments) {
    $return= ['options' => new RuntimeOptions(), 'bootstrap' => null, 'main' => null];
    while (null !== ($argument= array_shift($arguments))) {
      if ('-' !== $argument{0}) {
        $return['bootstrap']= trim($argument, '"\'');;
        break;
      } else if ('--' === $argument) {
        $return['bootstrap']= trim(array_shift($arguments), '"\'');
        break;
      }
      switch ($argument{1}) {
        case 'q':     // quiet
        case 'n':     // No php.ini file will be used
        case 'C': {   // [cgi] Do not chdir to the script's directory
          $return['options']->withSwitch($argument{1});
          break;
        }

        case 'd': {
          if (2 === strlen($argument)) {
            sscanf(array_shift($arguments), "%[^=]=%[^\r]", $setting, $value);
          } else {
            sscanf($argument, "-d%[^=]=%[^\r]", $setting, $value);
            $setting= ltrim($setting, ' ');
          }
          $return['options']->withSetting($setting, $value, true);
          break;
        }

        default: {
          throw new FormatException('Unrecognized argument "'.$argument.'"');
        }
      }
    }

    $main= array_shift($arguments);
    if ('' === $main) {
      $return['main']= XPClass::forName(array_shift($arguments));
    } else if ($main) {
      $return['main']= XPClass::forName($main);
    }
    return $return;
  }
  
  /**
   * Returns startup information
   *
   * @param   string selector
   * @return  var
   */
  protected function startup($selector) {
    if (null === $this->startup) {        // Lazy-init

      // Workaround for systems where args can only be unreliably be
      // parsed from "ps" output. This is detected by the runners and
      // the XP_CMDLINE environment variable will hold the command line.
      if ($cmdline= getenv('XP_CMDLINE')) {
        $this->startup= self::parseArguments(explode('|', $cmdline));
        $this->startup['env']= true;
      } else {
        $this->startup= self::parseArguments($this->getExecutable()->getArguments());
        $this->startup['env']= false;
      }
    }

    return $this->startup[$selector];
  }
  
  /**
   * Get startup options
   *
   * @return  lang.RuntimeOptions
   */
  public function startupOptions() {
    return clone $this->startup('options');
  }

  /**
   * Get bootstrap script's filename
   *
   * @param   string which default NULL
   * @return  string
   */
  public function bootstrapScript($which= null) {
    if ($which) {
      return dirname($this->startup('bootstrap')).DIRECTORY_SEPARATOR.$which.'-main.php';
    } else {
      return $this->startup('bootstrap');
    }
  }

  /**
   * Get entry point class
   *
   * @return  lang.XPClass
   */
  public function mainClass() {
    return $this->startup('main');
  }

  /**
   * Retrieve the executable associated with this runtime.
   *
   * @return  lang.Process
   */
  public function getExecutable() {
    if (null === $this->executable) {     // Lazy-init
      $this->executable= Process::getProcessById(
        getmypid(),
        defined('PHP_BINARY') ? constant('PHP_BINARY') : null
      );
    }
    return $this->executable;
  }

  /**
   * Create a new runtime instance.
   *
   * @param   lang.RuntimeOptions options default NULL
   * @param   string bootstrap default 'class'
   * @param   string class default NULL entry point class
   * @param   string[] arguments default []
   * @param   string cwd default NULL the working directory
   * @param   [:string] default NULL the environment
   * @return  lang.Process
   */
  public function newInstance(
    RuntimeOptions $options= null, 
    $bootstrap= 'class', 
    $class= null, 
    $arguments= [], 
    $cwd= null, 
    $env= null
  ) {
  
    // Use unmodified startup options if none are passed
    if (null === $options) {
      $options= $this->startupOptions();
    }
    
    // Merge together options (overwriting include_path which is misused
    // by the XP runners to transport scan_path, but since we're invoking
    // PHP directly here, expand it), and, if present, the bootstrap 
    // script and entry point class.
    $include= '.'.PATH_SEPARATOR.get_include_path();
    
    // If invocation uses a bootstrap mode, add the scanpath separator
    // in front of the includes - this will be stripped off by the
    // runner.
    if (null !== $bootstrap) {
      $include= '.'.PATH_SEPARATOR.PATH_SEPARATOR.$include;
    }
    if ($cp= $options->getClassPath()) {
      $include.= PATH_SEPARATOR.implode(PATH_SEPARATOR, $cp); 
    }
    $cmdline= array_merge(
      $options->withSetting('include_path', $include)->asArguments(),
      $bootstrap ? [$this->bootstrapScript($bootstrap)] : [],
      $class ? [$class] : []
    );
    
    // Pass XP_CMDLINE via environment - part 2 of workaround from above,
    // see inline comment in startup() method for details
    if ($this->startup('env')) {
      putenv('XP_CMDLINE='.implode('|', $cmdline));
    }

    // Finally, fork executable
    return $this->getExecutable()->newInstance(array_merge($cmdline, $arguments), $cwd, $env);
  }
}
