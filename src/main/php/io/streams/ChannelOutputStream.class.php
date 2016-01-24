<?php namespace io\streams;

use io\IOException;

/**
 * Output stream that writes to one of the "stdout", "stderr", "output"
 * channels provided as PHP input/output streams.
 *
 * @test  xp://net.xp_framework.unittest.io.streams.ChannelStreamTest
 * @see   php://wrappers
 * @see   xp://io.streams.ChannelInputStream
 */
class ChannelOutputStream implements OutputStream {
  protected
    $name = null,
    $fd   = null;

  /**
   * Constructor
   *
   * @param   string $arg Either a name or the file descriptor
   */
  public function __construct($arg) {
    if ('stdout' === $arg || 'stderr' === $arg || 'output' === $arg) {
      if (!($this->fd= fopen('php://'.$arg, 'rb'))) {
        throw new IOException('Could not open '.$arg.' channel for reading');
      }
    } else if (is_resource($arg)) {
      $this->fd= $arg;
      $this->name= '#'.(int)$arg;
    } else {
      throw new IOException('Expecting either stdout, stderr, output or a file descriptor '.typeof($arg).' given');
    }
  }

  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) { 
    if (false === fwrite($this->fd, $arg)) {
      $e= new IOException('Could not write '.strlen($arg).' bytes to '.$this->name.' channel');
      \xp::gc(__FILE__);
      throw $e;
    }
  }

  /**
   * Flush this stream.
   *
   */
  public function flush() {
    fflush($this->fd);
  }

  /**
   * Close this stream
   *
   */
  public function close() {
    fclose($this->fd);
  }

  /**
   * Creates a string representation of this input stream
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'(channel='.$this->name.')';
  }
}
