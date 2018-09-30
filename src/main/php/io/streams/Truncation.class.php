<?php namespace io\streams;

interface Truncation {
  
  /**
   * Truncate this stream to a given new size.
   *
   * @param  int $size
   * @return void
   */
  public function truncate($size);

}