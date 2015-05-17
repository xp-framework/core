<?php namespace io\streams;

/**
 * Writes data to an OutputStream
 */
interface OutputStreamWriter {

  /**
   * Constructor
   *
   * @param   io.streams.OutputStream out
   */
  public function __construct($out);

  /**
   * Flush output buffer
   *
   */
  public function flush();

  /**
   * Print arguments
   *
   * @param   var* args
   */
  public function write();
  
  /**
   * Print arguments and append a newline
   *
   * @param   var* args
   */
  public function writeLine();
  
  /**
   * Print a formatted string
   *
   * @param   string format
   * @param   var* args
   * @see     php://writef
   */
  public function writef();

  /**
   * Print a formatted string and append a newline
   *
   * @param   string format
   * @param   var* args
   */
  public function writeLinef();

}
