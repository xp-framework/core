<?php namespace xp\runtime;

use util\cmd\Console;
use lang\XPClass;

/**
 * Evaluates code and dumps its output.
 */
class Dump {

  /**
   * Main
   *
   * @param   string[] args
   */
  public static function main(array $args) {
    $way= array_shift($args);

    // Read sourcecode from STDIN if no further argument is given
    if (empty($args)) {
      $code= new Code(file_get_contents('php://stdin'));
    } else if ('--' === $args[0]) {
      $code= new Code(file_get_contents('php://stdin'));
    } else {
      $code= new Code($args[0]);
    }

    // Perform
    $return= $code->run($code->expression(), [XPClass::nameOf(self::class)] + $args);
    switch ($way) {
      case '-w': Console::writeLine($return); break;
      case '-d': var_dump($return); break;
    }
  }
}
