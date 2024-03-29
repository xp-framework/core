<?php namespace xp\runtime;

use lang\ClassLoader;
use util\cmd\Console;

/**
 * Displays XP version and runtime information
 *
 * @see  https://www.freedesktop.org/software/systemd/man/os-release.html
 */
class Version {

  /** @return string */
  private static function xpVersion() { return 'XP/'.\xp::version(); }

  /** @return string */
  private static function runnersVersion() { return 'Runners/'.getenv('XP_VERSION'); }

  /** @return string */
  private static function phpVersion() { return 'PHP/'.phpversion(); }

  /** @return string */
  private static function engineVersion() { return 'Zend/'.zend_version(); }

  /** @return string */
  private function osVersion() {
    if ('Linux' === PHP_OS_FAMILY) {
      if (is_file('/etc/os-release')) {
        $rel= parse_ini_file('/etc/os-release');
        $code= $rel['VERSION_CODENAME'] ?? '';
        return 'Linux/'.rtrim(isset($rel['PRETTY_NAME']) ? $rel['PRETTY_NAME'].' '.$code : $rel['NAME'].' '.$rel['VERSION']);
      } else if (is_executable('/usr/bin/lsb_release')) {
        return 'Linux/'.strtr(`/usr/bin/lsb_release -scd`, "\n", ' ');
      }
    } else if ('Darwin' === PHP_OS_FAMILY) {
      if (is_executable('/usr/bin/sw_vers')) {
        return 'Mac OS X/'.trim(`/usr/bin/sw_vers -productVersion`);
      }
    }

    return PHP_OS.'/'.php_uname('v');
  }

  /**
   * Main
   *
   * @param   string[] $args
   * @return  int
   */
  public static function main(array $args) {
    if (empty($args)) {
      Console::writeLinef(
        'XP %s { %s & %s } @ %s',
        \xp::version(),
        self::phpVersion(),
        self::engineVersion(),
        php_uname()
      );
      Console::writeLine('Copyright (c) 2001-', date('Y'), ' the XP group');
      foreach (ClassLoader::getLoaders() as $delegate) {
        Console::writeLine($delegate->toString());
      }
      return 1;
    } else {
      foreach ($args as $arg) {
        $method= $arg.'Version';
        if (is_callable(['self', $method])) {
          Console::writeLine(self::$method());
        } else {
          Console::$err->writeLinef('Unkown version argument `%s\'', $arg);
        }
      }
      return 0;
    }
  }
}
