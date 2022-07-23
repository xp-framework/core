<?php namespace io\streams;

use io\IOException;
use lang\Value;
use util\Comparison;

/**
 * Input stream that reads from one of the "stdin", "input" channels
 * provided as PHP input/output streams.
 *
 * @test  net.xp_framework.unittest.io.streams.ChannelStreamTest
 * @see   php://wrappers
 * @see   io.streams.ChannelOutputStream
 */
class ChannelInputStream implements InputStream, Value {
  use Comparison;

  protected $fd, $name;

  /**
   * Constructor
   *
   * @param   string $arg Either a name or the file descriptor
   */
  public function __construct($arg) {
    if ('stdin' === $arg || 'input' === $arg) {
      if (!($this->fd= fopen('php://'.$arg, 'rb'))) {
        throw new IOException('Could not open '.$arg.' channel for reading');
      }
      $this->name= $arg;
    } else if (is_resource($arg)) {
      $this->fd= $arg;
      $this->name= '#'.(int)$arg;
    } else {
      throw new IOException('Expecting either stdin, input or a file descriptor '.typeof($arg).' given');
    }
  }

  /**
   * Read a string
   *
   * @param   int limit default 8192
   * @return  string
   */
  public function read($limit= 8192) {
    if (null === $this->fd || false === ($bytes= fread($this->fd, $limit))) {
      $e= new IOException('Could not read '.$limit.' bytes from '.$this->name.' channel');
      \xp::gc(__FILE__);
      throw $e;
    }
    return $bytes;
  }

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   */
  public function available() {
    return null === $this->fd || feof($this->fd) ? 0 : 1;
  }

  /**
   * Close this input stream
   *
   */
  public function close() {
    if ($this->fd) {
      fclose($this->fd);
      $this->fd= null;
    }
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
