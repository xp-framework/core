<?php namespace xp\runtime;

use util\cmd\Console;

/**
 * Displays XP version and runtime information
 */
class Version {

  /** @return string */
  private function xpVersion() { return 'XP/'.\xp::version(); }

  /** @return string */
  private function phpVersion() { return 'PHP/'.phpversion(); }

  /** @return string */
  private function engineVersion() { return defined('HHVM_VERSION') ? 'HHVM/'.HHVM_VERSION : 'Zend/'.zend_version(); }

  /** @return string */
  private function osVersion() {
    if ('Linux' === PHP_OS) {
      if (is_file('/etc/os-release')) {
        $rel= parse_ini_file('/etc/os-release');
        return 'Linux/'.($rel['PRETTY_NAME'] ?: $rel['NAME'].' '.$rel['VERSION']);
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
    if (isset($args[0])) {
      $method= $args[0].'Version';
      Console::writeLine(self::$method());
    } else {
      Console::writeLinef(
        'XP %s { PHP %s & ZE %s } @ %s',
        \xp::version(),
        phpversion(),
        zend_version(),
        php_uname()
      );
      Console::writeLine('Copyright (c) 2001-2016 the XP group');
      foreach (\lang\ClassLoader::getLoaders() as $delegate) {
        Console::writeLine($delegate->toString());
      }
      return 1;
    }
  }
}
