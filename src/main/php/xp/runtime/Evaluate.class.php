<?php namespace xp\runtime;

use util\cmd\Console;
use lang\XPClass;

/**
 * Evaluates sourcecode
 *
 */
class Evaluate extends \lang\Object {
  
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
    } else {
      $code= new Code($args[0]);
    }

    // Perform
    $argv= [XPClass::nameOf(__CLASS__)] + $args;
    return eval($code->head().$code->fragment());
  }
}
