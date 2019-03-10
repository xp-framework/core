<?php namespace xp\runtime;

use lang\{XPClass, Environment};
use util\cmd\Console;

/**
 * Evaluates sourcecode. Used by `xp eval` subcommand.
 */
class Evaluate {
  
  /**
   * Main
   *
   * @param  string[] $args
   * @return int
   */
  public static function main(array $args) {

    // Read sourcecode from STDIN if no further argument is given
    if (empty($args)) {
      $code= new Code(file_get_contents('php://stdin'), '(standard input)');
    } else if ('--' === $args[0]) {
      $code= new Code(file_get_contents('php://stdin'), '(standard input)');
    } else if (is_file($args[0])) {
      $code= new Code(file_get_contents($args[0]), $args[0]);
    } else {
      $code= new Code($args[0], '(command line argument)');
    }

    try {
      return $code->run([XPClass::nameOf(self::class)] + $args);
    } catch (CouldNotLoadDependencies $e) {
      $modules= $code->modules();
      $dir= $modules->userDir($code->namespace());

      Console::$err->writeLine("\033[41;1;37mError: ", $e->getMessage(), "\033[0m\n");
      Console::$err->writeLine("To install the missing dependencies, use:\n\n\033[36mmkdir -p '", $dir, "'");
      foreach ($e->modules() as $module) {
        $version= $modules->version($module);
        Console::$err->writeLinef("composer require -d '%s' %s%s", $dir, $module, $version ? " '$version'" : '');
      }
      Console::$err->write("\033[0m");
      return 127;
    }
  }
}
