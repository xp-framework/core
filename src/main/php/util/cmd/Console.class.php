<?php namespace util\cmd;

use io\streams\{StringWriter, StringReader, ConsoleOutputStream, ConsoleInputStream};

/**
 * Represents system console
 *
 * Example: Writing to standard output
 * ```php
 * use util\cmd\Console;
 *
 * Console::writeLine('Hello ', 'a', 'b', 1);   // Hello ab1
 * Console::writeLinef('Hello %s', 'World');    // Hello World
 * Console::$out->write('.');
 * ```
 *
 * Example: Writing to standard error
 * ```php
 * use util\cmd\Console;
 *
 * Console::$err->writeLine('*** An error occured: ', $e->toString());
 * ```
 *
 * @test  xp://net.xp_framework.unittest.util.cmd.ConsoleTest
 * @see   http://msdn.microsoft.com/library/default.asp?url=/library/en-us/cpref/html/frlrfSystemConsoleClassTopic.asp
 */
abstract class Console {
  public static 
    $out= null,
    $err= null,
    $in = null;

  static function __static() {
    self::initialize(defined('STDIN'));
  }

  /**
   * Initialize streams
   *
   * @param  bool $console
   */
  public static function initialize($console) {
    if ($console) {
      self::$in= new StringReader(new ConsoleInputStream(STDIN));
      self::$out= new StringWriter(new ConsoleOutputStream(STDOUT));
      self::$err= new StringWriter(new ConsoleOutputStream(STDERR));
    } else {
      self::$in= new NoInput();
      self::$out= new NoOutput();
      self::$err= new NoOutput();
    }
  }

  /**
   * Flush output buffer
   *
   * @return void
   */
  public static function flush() {
    self::$out->flush();
  }

  /**
   * Write a string to standard output
   *
   * @param  var... $args
   * @return void
   */
  public static function write(... $args) {
    self::$out->write(...$args);
  }
  
  /**
   * Write a string to standard output and append a newline
   *
   * @param  var... $args
   * @return void
   */
  public static function writeLine(... $args) {
    self::$out->writeLine(...$args);
  }
  
  /**
   * Write a formatted string to standard output
   *
   * @param  string $format
   * @param  var... $args
   * @return void
   */
  public static function writef($format, ... $args) {
    self::$out->writef($format, ...$args);
  }

  /**
   * Write a formatted string to standard output and append a newline
   *
   * @param  string $format
   * @param  var... $args
   * @return void
   */
  public static function writeLinef($format, ... $args) {
    self::$out->writeLinef($format, ...$args);
  }
  
  /**
   * Read a line from standard input. The line ending (\r and/or \n)
   * is trimmed off the end.
   *
   * @param  string $prompt Optional prompt
   * @return string
   */    
  public static function readLine(string $prompt= null) {
    $prompt && self::$out->write($prompt.' ');
    return self::$in->readLine();
  }

  /**
   * Read a single character from standard input.
   *
   * @param  string $prompt Optional prompt
   * @return string
   */    
  public static function read(string $prompt= null) {
    $prompt && self::$out->write($prompt.' ');
    return self::$in->read(1);
  }
}
