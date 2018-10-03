<?php namespace xp\runtime;

use lang\XPClass;
use util\cmd\Console;

/**
 * Evaluates code and dumps its output.
 */
class Dump {

  /**
   * Main
   *
   * @param  string[] $args
   * @return int
   */
  public static function main(array $args) {
    $way= array_shift($args);

    // Read sourcecode from STDIN if no further argument is given
    if (empty($args)) {
      $code= new Code(file_get_contents('php://stdin'), '(standard input)');
    } else if ('--' === $args[0]) {
      $code= new Code(file_get_contents('php://stdin'), '(standard input)');
    } else {
      $code= new Code($args[0], '(command line argument)');
    }

    // Perform
    $return= $code->withReturn()->run([XPClass::nameOf(self::class)] + $args);
    switch ($way) {
      case '-w': Console::writeLine($return); break;
      case '-d': var_dump($return); break;
    }
  }
}
