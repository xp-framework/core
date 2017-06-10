<?php namespace io\streams;

/**
 * InputStream that reads from the console
 *
 * Usage:
 * ```php
 * $in= new ConsoleInputStream(STDIN);
 * ```
 */
class ConsoleInputStream implements InputStream {
  private $descriptor, $close;
  
  /**
   * Constructor
   *
   * @param  var $descriptor STDIN
   * @param  bool $close
   */
  public function __construct($descriptor, $close= false) {
    $this->descriptor= $descriptor;
    $this->close= $close;
  }

  /**
   * Creates a string representation of this Input stream
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<'.$this->descriptor.'>';
  }

  /**
   * Read a string
   *
   * @param   int limit default 8192
   * @return  string
   */
  public function read($limit= 8192) {
    $c= fread($this->descriptor, $limit);
    return $c;
  }

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   */
  public function available() {
    return feof($this->descriptor) ? 0 : 1;
  }
  
  /**
   * Close this buffer.
   *
   */
  public function close() {
    if ($this->close) {
      fclose($this->descriptor);
    }
  }
}
