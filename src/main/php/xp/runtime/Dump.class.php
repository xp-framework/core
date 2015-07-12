<?php namespace xp\runtime;

use util\cmd\Console;
use lang\XPClass;

/**
 * Evaluates code and dumps its output.
 */
class Dump extends \lang\Object {

  /**
   * Main
   *
   * @param   string[] args
   */
  public static function main(array $args) {
    $way= array_shift($args);

    // Read sourcecode from STDIN if no further argument is given
    if (0 === sizeof($args)) {
      $code= new Code(file_get_contents('php://stdin'));
    } else {
      $code= new Code($args[0]);
    }

    // Perform
    $argv= [XPClass::nameOf(__CLASS__)] + $args;
    $argc= sizeof($argv);
    $return= eval($code->head().$code->expression());
    switch ($way) {
      case '-w': Console::writeLine($return); break;
      case '-d': var_dump($return); break;
    }
  }
}
