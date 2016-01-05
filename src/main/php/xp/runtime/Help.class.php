<?php namespace xp\runtime;

use util\cmd\Console;
use lang\XPClass;

/**
 * Shows help
 */
class Help {

  /**
   * Converts api-doc "markup" to plain text w/ ASCII "art"
   *
   * @param   string markup
   * @return  string text
   */
  private static function textOf($markup) {
    $line= str_repeat('=', 72);
    return strip_tags(preg_replace(
      ['#```([a-z]*)#', '#```#', '#^\- #'],
      [$line, $line, '* '],
      trim($markup)
    ));
  }

  /**
   * Main
   *
   * @param   string[] args
   */
  public static function main(array $args) {
    $class= XPClass::forName($args[0]);
    Console::writeLine('@', $class->getClassLoader());
    Console::writeLine(self::textOf($class->getComment()));
  }
}
