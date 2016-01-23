<?php namespace util\cmd;

use io\streams\StringWriter;
use io\streams\StringReader;
use io\streams\ConsoleOutputStream;
use io\streams\ConsoleInputStream;

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
class Console extends \lang\Object {
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
   * @param  bool console
   */
  public static function initialize($console) {
    if ($console) {
      self::$in= new StringReader(new ConsoleInputStream(STDIN));
      self::$out= new StringWriter(new ConsoleOutputStream(STDOUT));
      self::$err= new StringWriter(new ConsoleOutputStream(STDERR));
    } else {
      self::$in= newinstance('io.streams.InputStreamReader', [null], '{
        public function __construct($in) { }
        public function getStream() { return null; }
        public function raise() { throw new \lang\IllegalStateException("There is no console present"); }
        public function read($count= 8192) { $this->raise(); }
        public function readLine() { $this->raise(); }
      }');
      self::$out= self::$err= newinstance('io.streams.OutputStreamWriter', [null], '{
        public function __construct($out) { }
        public function getStream() { return null; }
        public function flush() { $this->raise(); }
        public function raise() { throw new \lang\IllegalStateException("There is no console present"); }
        public function write(... $args) { $this->raise(); }
        public function writeLine(... $args) { $this->raise(); }
        public function writef($format, ... $args) { $this->raise(); }
        public function writeLinef($format, ... $args) { $this->raise(); }
      }');
    }
  }

  /**
   * Flush output buffer
   *
   * @return  void
   */
  public static function flush() {
    self::$out->flush();
  }

  /**
   * Write a string to standard output
   *
   * @param   var... args
   */
  public static function write(... $args) {
    self::$out->write(...$args);
  }
  
  /**
   * Write a string to standard output and append a newline
   *
   * @param   var... args
   */
  public static function writeLine(... $args) {
    self::$out->writeLine(...$args);
  }
  
  /**
   * Write a formatted string to standard output
   *
   * @param   string format
   * @param   var... args
   * @see     php://printf
   */
  public static function writef($format, ... $args) {
    self::$out->writef($format, ...$args);
  }

  /**
   * Write a formatted string to standard output and append a newline
   *
   * @param   string format
   * @param   var* args
   */
  public static function writeLinef($format, ... $args) {
    self::$out->writeLinef($format, ...$args);
  }
  
  /**
   * Read a line from standard input. The line ending (\r and/or \n)
   * is trimmed off the end.
   *
   * @param   string prompt = NULL
   * @return  string
   */    
  public static function readLine($prompt= null) {
    $prompt && self::$out->write($prompt.' ');
    return self::$in->readLine();
  }

  /**
   * Read a single character from standard input.
   *
   * @param   string prompt = NULL
   * @return  string
   */    
  public static function read($prompt= null) {
    $prompt && self::$out->write($prompt.' ');
    return self::$in->read(1);
  }
}
