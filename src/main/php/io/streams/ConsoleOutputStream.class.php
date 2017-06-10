<?php namespace io\streams;

/**
 * OuputStream that writes to the console
 *
 * Usage:
 * ```php
 * $out= new ConsoleOutputStream(STDOUT);
 * $err= new ConsoleOutputStream(STDERR);
 * ```
 */
class ConsoleOutputStream implements OutputStream {
  private $descriptor, $close;
  
  /**
   * Constructor
   *
   * @param  var $descriptor one of STDOUT, STDERR
   * @param  bool $close
   */
  public function __construct($descriptor, $close= false) {
    $this->descriptor= $descriptor;
    $this->close= $close;
  }

  /**
   * Creates a string representation of this output stream
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<'.$this->descriptor.'>';
  }

  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) { 
    fwrite($this->descriptor, $arg);
  }

  /**
   * Flush this buffer.
   *
   */
  public function flush() { 
    fflush($this->descriptor);
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
