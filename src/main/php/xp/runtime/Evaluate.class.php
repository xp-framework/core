<?php namespace xp\runtime;

use lang\{XPClass, ClassNotFoundException, Environment};
use util\cmd\Console;

/** Evaluates sourcecode. Used by `xp eval` subcommand */
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
    } catch (ClassNotFoundException $e) {
      $dir= $code->modules()->userDir($code->namespace());
      $t= $e->getStackTrace()[0];

      Console::$err->writeLine("\033[41;1;37m", $e->getMessage(), " @ {$t->file}:{$t->line}\033[0m\n");
      Console::$err->writeLine("To ensure all dependencies are up-to-date, use:\n\033[36m");
      Console::$err->writeLinef("composer update -d '{$dir}'");
      Console::$err->write("\033[0m");
      return 255;
    } catch (CouldNotLoadDependencies $e) {
      $modules= $code->modules();
      $dir= $code->modules()->userDir($code->namespace());

      Console::$err->writeLine("\033[41;1;37m", $e->getMessage(), "\033[0m\n");
      Console::$err->writeLine("To install the missing dependencies, use:\n\033[36m");
      Console::$err->writeLine("mkdir -p '{$dir}'");
      foreach ($e->modules() as $module) {
        $version= $modules->version($module);
        Console::$err->writeLine("composer require -d '{$dir}' {$module}", $version ? " '{$version}'" : '');
      }
      Console::$err->write("\033[0m");
      return 127;
    }
  }
}
