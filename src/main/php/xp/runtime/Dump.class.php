<?php namespace xp\runtime;

use util\cmd\Console;
use lang\XPClass;

/**
 * Evaluates code and dumps its output.
 *
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
      $src= file_get_contents('php://stdin');
    } else {
      $src= $args[0];
    }
    $src= trim($src, ' ;').';';
    
    // Allow missing return
    strstr($src, 'return ') || strstr($src, 'return;') || $src= 'return '.$src;

    // Rewrite argc, argv
    $argv= array(XPClass::nameOf(__CLASS__)) + $args;
    $argc= sizeof($argv);

    // Perform
    $return= eval($src);
    switch ($way) {
      case '-w': Console::writeLine($return); break;
      case '-d': var_dump($return); break;
    }
  }
}
