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
    foreach ($_ENV as $name => $value) {
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
    if (null === $filter) return $_ENV;

    $r= [];
    if (is_array($filter)) {
      foreach ($filter as $name) {
        isset($_ENV[$name]) && $r[$name]= $_ENV[$name];
      }
    } else {
      foreach ($_ENV as $name => $value) {
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
        unset($_ENV[$name]);
      } else {
        putenv($name.'='.$value);
        $_ENV[$name]= $value;
      }
    }
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
  public static function configDir(string $named= null, $home= null): string {
    $home ?? $home= getenv('HOME');

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
    if (0 === strncasecmp(PHP_OS, 'Win', 3)) {
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