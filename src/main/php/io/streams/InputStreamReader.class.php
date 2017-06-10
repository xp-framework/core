<?php namespace io\streams;

/**
 * Reads data from an InputStream
 */
interface InputStreamReader {

  /**
   * Read a number of bytes
   *
   * @param   int size default 8192
   * @return  string
   */
  public function read($size= 8192);

  /**
   * Read an entire line
   *
   * @return  string
   */
  public function readLine();
}
