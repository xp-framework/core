<?php namespace xp\runtime;

use util\cmd\Console;
use lang\XPClass;

/**
 * Evaluates sourcecode. Used by `xp eval` subcommand.
 */
class Evaluate {
  
  /**
   * Main
   *
   * @param   string[] args
   */
  public static function main(array $args) {
    $argc= sizeof($args);

    // Read sourcecode from STDIN if no further argument is given
    if (0 === $argc) {
      $code= new Code(file_get_contents('php://stdin'));
    } else if ('--' === $args[0]) {
      $code= new Code(file_get_contents('php://stdin'));
    } else if (is_file($args[0])) {
      $code= new Code(file_get_contents($args[0]));
    } else {
      $code= new Code($args[0]);
    }

    // Perform
    $argv= [XPClass::nameOf(self::class)] + $args;
    return eval($code->head().$code->fragment());
  }
}
