<?php namespace lang;

/**
 * User environment
 *
 * @test  xp://net.xp_framework.unittest.core.EnvironmentTest
 */
abstract class Environment {

  /**
   * Checks whether the environment is XDG compliant
   *
   * @see    https://specifications.freedesktop.org/basedir-spec/basedir-spec-latest.html
   * @return bool
   */
  private static function xdgCompliant() {
    foreach (getenv() as $name => $value) {
      if (0 === strncmp($name, 'XDG_', 4)) return true;
    }
    return false;
  }

  /**
   * Gets all variables. Accepts either a regular expression to search for
   * or an array of names as optional filter argument.
   *
   * @see     php://preg_match
   * @param   string|string[] $filter Optional filter on names
   * @return  [:string]
   */
  public static function variables($filter= null) {
    $variables= getenv();
    if (null === $filter) return $variables;

    $r= [];
    if (is_array($filter)) {
      foreach ($filter as $name) {
        isset($variables[$name]) && $r[$name]= $variables[$name];
      }
    } else {
      foreach ($variables as $name => $value) {
        preg_match($filter, $name) && $r[$name]= $value;
      }
    }
    return $r;
  }

  /**
   * Gets the value of an environment variable. Accepts an optional default,
   * which can either be a closure to be executed or any other value to be 
   * returned when none of the given names is found.
   *
   * @param  string|string[] $arg One or more names to test
   * @param  var... $default Optional default
   * @return string
   * @throws lang.IllegalArgumentException If none of the names is found
   */
  public static function variable($arg, ... $default) {
    foreach ((array)$arg as $name) {
      if (false === ($env= getenv($name))) continue;
      return $env;
    }

    if (empty($default)) {
      throw new IllegalArgumentException(is_array($arg)
        ? 'None of the variables [$'.implode(', $', $arg).'] exists'
        : 'No such environment variable $'.$name
      );
    } else if ($default[0] instanceof \Closure) {
      return $default[0]($arg);
    } else {
      return $default[0];
    }
  }

  /**
   * Exports given environment variables
   *
   * @param  [:string] $names
   * @return void
   */
  public static function export($variables) {
    foreach ($variables as $name => $value) {
      if (null === $value) {
        putenv($name);
        unset($_SERVER[$name]);
      } else {
        putenv($name.'='.$value);
        $_SERVER[$name]= $value;
      }
    }
  }

  /**
   * Returns the platform we're currently operating on, which is one of:
   *
   * - Windows
   * - Linux
   * - Darwin ("Mac OS")
   * - BSD
   * - Solaris
   * - Cygwin
   * - Unknown
   * 
   * Same as the constant `PHP_OS_FAMILY` but handles Cygwin.
   */
  public static function platform(): string {
    return 'Windows' === PHP_OS_FAMILY
      ? getenv('HOME') ? 'Cygwin' : 'Windows'
      : PHP_OS_FAMILY
    ;
  }

  /**
   * Returns a path for display for a given directory. Will replace current
   * and parent directories with `.` and `..`, the user's home directory with
   * `~` and use `%USERPROFILE%` and `%APPDATA%` on Windows.
   *
   * Platform accepts one of the values returned from `platform()` or NULL
   * to use the platform we're currently operating on.
   */
  public static function path(string $dir= '.', string $platform= null): string {
    if ('.' === $dir || '..' === $dir) return $dir; // Short-circuit well-known names

    // Based on platform, define shorthands for replacements
    $cwd= getcwd();
    $replace= [$cwd => '.', dirname($cwd) => '..'];
    switch ($platform ?? self::platform()) {
      case 'Windows':
        $separator= '\\';
        $replace+= [getenv('APPDATA') => '%APPDATA%', getenv('USERPROFILE') => '%USERPROFILE%'];
        break;

      case 'Cygwin':
        $separator= '/';
        $replace+= [getenv('HOME') => '~', getenv('APPDATA') => '$APPDATA', getenv('USERPROFILE') => '$USERPROFILE'];
        break;

      default:
        $separator= '/';
        $replace+= [getenv('HOME') => '~'];
        break;
    }

    // Short-circuit paths without directory
    if (strcspn($dir, '/\\') === strlen($dir)) return '.'.$separator.$dir;

    // Compare expanded paths against replace using case-insensitivity on Windows
    $prefix= 'Windows' === PHP_OS_FAMILY ? 'stripos' : 'strpos';
    $expand= function($path) {
      return realpath($path) ?: rtrim(strtr($path, '/\\', DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
    };

    $path= $expand($dir);
    foreach ($replace as $base => $with) {
      if (0 === $prefix($path, $expand($base))) {
        $path= $with.substr($path, strlen($base));
        break;
      }
    }
    return strtr($path, DIRECTORY_SEPARATOR, $separator);
  }

  /**
   * Retrieve location of temporary directory. This method looks at the 
   * environment variables TEMP, TMP, TMPDIR and TEMPDIR and, if these 
   * cannot be found, uses PHP's builtin functionality.
   */
  public static function tempDir(): string {
    $dir= self::variable(['TEMP', 'TMP', 'TMPDIR', 'TEMPDIR'], function() { return sys_get_temp_dir(); });
    return rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
  }

  /** Returns current user's home directory */
  public static function homeDir(): string {
    return rtrim(self::variable(['HOME', 'USERPROFILE']), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
  }

  /**
   * Returns current user's configuration directory
   *
   * - $APPDATA/{Named} on Windows
   * - $XDG_CONFIG_HOME/{named} inside an XDG environment
   * - $HOME/.{named} otherwise
   *
   * Pass NULL to retrieve configuration base directory
   */
  public static function configDir(string $named= null, string $home= null): string {
    $home??= getenv('HOME');

    if (!$home) {
      $base= getenv('APPDATA');
      $dir= ucfirst($named ?? '');
    } else if (self::xdgCompliant()) {
      $base= getenv('XDG_CONFIG_HOME') ?: $home.DIRECTORY_SEPARATOR.'.config';
      $dir= $named;
    } else {
      $base= $home;
      $dir= '.'.$named;
    }

    return rtrim($named ? $base.DIRECTORY_SEPARATOR.$dir : $base, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
  }

  /**
   * Returns certificates trusted by this system. Searches for a file called:
   *
   * - `$SSL_CERT_FILE`
   * - `ca-bundle.crt` in the user's config dir named "xp"
   * - `ca-bundle.crt` alongside *xp.exe* (provided by "cert" utility)
   *
   * If not found, it test various well-known system-wide locations on Unix and
   * Linux systems as well as Cygwin installation paths on Windows.
   *
   * @see    https://github.com/xp-framework/core/issues/150
   * @see    https://github.com/xp-runners/cert
   * @param  string $default
   * @return string The file path, or the value of $default
   * @throws lang.SystemException If nothing is found and no default is given
   */
  public static function trustedCertificates($default= null) {
    static $search= [
      'un*x' => [
        '/etc/ssl/certs/ca-certificates.crt',     // Debian/Ubuntu/Gentoo etc.
        '/etc/pki/tls/certs/ca-bundle.crt',       // Fedora/RHEL
        '/etc/ssl/ca-bundle.pem',                 // OpenSUSE
        '/etc/pki/tls/cacert.pem',                // OpenELEC
        '/usr/local/share/certs/ca-root-nss.crt', // FreeBSD/DragonFly
        '/etc/ssl/cert.pem',                      // OpenBSD
        '/etc/openssl/certs/ca-certificates.crt'  // NetBSD
      ],
      'cygwin' => [
        '\etc\pki\ca-trust\extracted\pem\tls-ca-bundle.pem'
      ]
    ];

    if (is_file($certs= getenv('SSL_CERT_FILE'))) {
      return $certs;
    }

    if (is_file($bundle= self::configDir('xp').'ca-bundle.crt')) {
      return $bundle;
    }

    if ($env= getenv('XP_EXE')) {
      $bundle= dirname($env).DIRECTORY_SEPARATOR.'ca-bundle.crt';
      if (is_file($bundle)) return $bundle;
    }

    // Search well-known locations
    if ('Windows' === PHP_OS_FAMILY) {
      $base= dirname(getenv('HOME'));
      while (strlen($base) > 3 && !is_file($base.'\bin\cygwin1.dll')) {
        $base= dirname($base);
      }
      foreach ($search['cygwin'] as $file) {
        if (is_file($bundle= $base.$file)) return $bundle;
      }
    } else {
      foreach ($search['un*x'] as $bundle) {
        if (is_file($bundle)) return $bundle;
      }
    }

    if (null !== $default) return $default;
    throw new SystemException('No ca-bundle.crt found', 2);
  }
}