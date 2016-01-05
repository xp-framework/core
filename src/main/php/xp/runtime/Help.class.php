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
   * @param  string $markup
   * @return string text
   */
  private static function textOf($markup) {
    $line= str_repeat('=', 72);
    return strip_tags(preg_replace(
      [
        '#(.+)\n=+\n#m',                // Underlined first-level headline
        '#\# (.+)#',                    // Underlined first-level headline
        '#```([a-z]*)\n(.+)\n```#ms',   // Code section
        '#\{([^\}]+)\}#',               // {placeholder}
        '#"([^"]+)"#',                  // "string"
        '#^\* \* \*#',                  // horizontal rule
        '#^\- #'                        // unordered list
      ],
      [
        "\e[1m".'$1'."\n\e[36m".$line."\e[0m\n",
        "\e[1m".'$1'."\n\e[36m".$line."\e[0m\n",
        "\e[44;1;37m".'$2'."\e[0m",
        "\e[33;1m{".'$1'."}\e[0m",
        "\e[36;1m".'$1'."\e[0m",
        $line,
        '* '
      ],
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
