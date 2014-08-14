<?php namespace io\streams;

/**
 * An InputStream can be read from
 *
 */
interface InputStream extends \lang\Closeable {

  /**
   * Read a string
   *
   * @param   int limit default 8192
   * @return  string
   */
  public function read($limit= 8192);

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   * @return int
   */
  public function available();
}
