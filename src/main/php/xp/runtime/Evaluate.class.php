<?php namespace xp\runtime;

use lang\XPClass;

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

    return $code->run([XPClass::nameOf(self::class)] + $args);
  }
}
