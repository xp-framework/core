<?php namespace io\streams;

/**
 * Writes data to an OutputStream
 */
interface OutputStreamWriter {

  /**
   * Flush output buffer
   *
   * @return void
   */
  public function flush();

  /**
   * Write arguments
   *
   * @param   var... args
   */
  public function write(... $args);
  
  /**
   * Write arguments and append a newline
   *
   * @param   var... args
   */
  public function writeLine(... $args);
  
  /**
   * Write a formatted string
   *
   * @param   string format
   * @param   var... args
   * @see     php://writef
   */
  public function writef($format, ... $args);

  /**
   * Write a formatted string and append a newline
   *
   * @param   string format
   * @param   var... args
   */
  public function writeLinef($format, ... $args);

}
