<?php namespace io\streams;

use lang\Closeable;

/** An InputStream can be read from */
interface InputStream extends Closeable {

  /**
   * Read a string
   *
   * @param  int $limit
   * @return string
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
