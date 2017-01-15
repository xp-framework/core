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
      $code= new Code(file_get_contents('php://stdin'));
    } else if ('--' === $args[0]) {
      $code= new Code(file_get_contents('php://stdin'));
    } else if (is_file($args[0])) {
      $code= new Code(file_get_contents($args[0]));
    } else {
      $code= new Code($args[0]);
    }

    return $code->run($code->fragment(), [XPClass::nameOf(self::class)] + $args);
  }
}
