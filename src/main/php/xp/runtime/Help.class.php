<?php namespace xp\runtime;

use util\cmd\Console;
use lang\XPClass;
use lang\ClassLoader;

/**
 * Shows help
 */
class Help {
  private static $colored;

  static function __static() {
    $line= str_repeat('═', 72);
    self::$colored= new RenderMarkdown([
      'h1'     => "\e[1m".'$1'."\n\e[36m".$line."\e[0m",
      'link'   => "\e[33;1m".'$1'."\e[0m (» \e[35;1m".'${SELF}/$2'."\e[0m)",
      'bold'   => "\e[33;1m".'$1'."\e[0m",
      'italic' => "\e[36;1m".'$1'."\e[0m",
      'pre'    => "\e[32;1m".'$1'."\e[0m",
      'code'   => "\n".'$1'."\e[44;1;37m".'$3'."\e[0m\n",
      'li'     => "\e[33;1m".'>'."\e[0m".' $2',
      'hr'     => $line
    ]);
  }

  /**
   * Render markdown to a stream
   *
   * @param  io.streams.StringWriter $writer
   * @param  string $markdown
   * @param  var $source E.g., a ClassLoader instance
   * @return void
   */
  public static function render($writer, $markdown, $source, $replace= []) {
    $source && $writer->writeLine("\e[33m@", $source, "\e[0m");
    $writer->writeLine(strtr(self::$colored->render($markdown), $replace));
  }

  /**
   * Main
   *
   * @param  string[] $args
   * @return int
   */
  public static function main(array $args) {
    $command= null;

    if (empty($args)) {
      $class= new XPClass(__CLASS__);
      $source= $class->getClassLoader();
      $markdown= $class->getComment();
    } else if ('@' === $args[0]{0}) {
      sscanf($args[0], '@%[^:]:%s', $resource, $command);
      if (null === ($source= ClassLoader::getDefault()->findResource($resource))) {
        Console::$err->writeLine('No help topic named ', $resource);
        return 2;
      }
      $markdown= $source->getResource($resource);
    } else {
      sscanf($args[0], '%[^:]:%s', $class, $command);
      if (null === ($source= ClassLoader::getDefault()->findClass($class))) {
        Console::$err->writeLine('No class named ', $class);
        return 2;
      }
      $markdown= $source->loadClass($class)->getComment();
    }

    self::render(Console::$out, $markdown, $source, ['${SELF}' => rtrim('xp help '.$command, ' ')]);
    return 1;
  }
}
