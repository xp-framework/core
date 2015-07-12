<?php namespace text\encode;

use io\streams\OutputStream;
use io\streams\Streams;

/**
 * OuputStream that encodes data to base64 encoding
 *
 * @see      rfc://2045 section 6.8 
 * @test     xp://net.xp_framework.unittest.text.encode.Base64OutputStreamTest
 */
class Base64OutputStream extends \lang\Object implements OutputStream {
  protected $out= null;
  
  /**
   * Constructor
   *
   * @param   io.streams.OutputStream out
   * @param   int lineLength limit maximum line length
   */
  public function __construct(OutputStream $out, $lineLength= 0) {
    $params= $lineLength ? ['line-length' => $lineLength, 'line-break-chars' => "\n"] : [];
    $this->out= Streams::writeableFd($out);
    if (!stream_filter_append($this->out, 'convert.base64-encode', STREAM_FILTER_WRITE, $params)) {
      throw new \io\IOException('Could not append stream filter');
    }
  }
  
  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) {
    fwrite($this->out, $arg);
  }

  /**
   * Flush this buffer
   *
   */
  public function flush() {
    fflush($this->out);
  }

  /**
   * Close this buffer. Flushes this buffer and then calls the close()
   * method on the underlying OuputStream.
   *
   */
  public function close() {
    fclose($this->out);
    $this->out= null;
  }

  /**
   * Destructor. Ensures output stream is closed.
   *
   */
  public function __destruct() {
    if (!$this->out) return;
    fclose($this->out);
    $this->out= null;
  }
}
