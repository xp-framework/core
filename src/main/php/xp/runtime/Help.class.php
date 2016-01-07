<?php namespace xp\runtime;

use util\cmd\Console;
use lang\XPClass;

/**
 * Shows help
 */
class Help {

  /**
   * Main
   *
   * @param  string[] $args
   * @return int
   */
  public static function main(array $args) {
    if (empty($args)) {
      $class= new XPClass(__CLASS__);
      $markdown= $class->getComment();
    } else if ('@' === $args[0]{0}) {
      $class= new XPClass(__CLASS__);
      $markdown= $class->getPackage()->getResource(substr($args[0], 1));
    } else {
      $class= XPClass::forName($args[0]);
      $markdown= $class->getComment();
    }

    $line= str_repeat('═', 72);
    $colored= new RenderMarkdown([
      'h1'     => "\e[1m".'$1'."\n\e[36m".$line."\e[0m",
      'bold'   => "\e[33;1m".'$1'."\e[0m",
      'italic' => "\e[36;1m".'$1'."\e[0m",
      'pre'    => "\e[32;1m".'$1'."\e[0m",
      'code'   => "\n".'$1'."\e[44;1;37m".'$3'."\e[0m\n",
      'li'     => "\e[33;1m".'>'."\e[0m".' $2',
      'hr'     => $line
    ]);

    Console::writeLine("\e[33m@", $class->getClassLoader(), "\e[0m");
    Console::writeLine($colored->render($markdown));
    return 1;
  }
}
