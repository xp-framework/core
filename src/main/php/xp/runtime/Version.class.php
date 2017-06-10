<?php namespace xp\runtime;

use util\cmd\Console;
use lang\ClassLoader;

/**
 * Displays XP version and runtime information
 *
 * @see  https://www.freedesktop.org/software/systemd/man/os-release.html
 */
class Version {

  /** @return string */
  private function xpVersion() { return 'XP/'.\xp::version(); }

  /** @return string */
  private function runnersVersion() { return 'Runners/'.getenv('XP_VERSION'); }

  /** @return string */
  private function phpVersion() { return 'PHP/'.phpversion(); }

  /** @return string */
  private function engineVersion() { return defined('HHVM_VERSION') ? 'HHVM/'.HHVM_VERSION : 'Zend/'.zend_version(); }

  /** @return string */
  private function osVersion() {
    if ('Linux' === PHP_OS) {
      if (is_file('/etc/os-release')) {
        $rel= parse_ini_file('/etc/os-release');
        $code= $rel['VERSION_CODENAME'] ?? '';
        return 'Linux/'.($rel['PRETTY_NAME'] ? $rel['PRETTY_NAME'].' '.$code : $rel['NAME'].' '.$rel['VERSION']);
      } else if (is_executable('/usr/bin/lsb_release')) {
        return 'Linux/'.strtr(`/usr/bin/lsb_release -scd`, "\n", ' ');
      }
    } else if ('Darwin' === PHP_OS) {
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
      Console::writeLine('Copyright (c) 2001-2017 the XP group');
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
