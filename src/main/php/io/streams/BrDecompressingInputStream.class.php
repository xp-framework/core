<?php namespace io\streams;

use lang\IllegalArgumentException;

/**
 * Brotli input stream
 *
 * @ext  brotli
 * @test net.xp_framework.unittest.io.streams.BrDecompressingInputStreamTest
 * @see  https://github.com/kjdev/php-ext-brotli
 */
class BrDecompressingInputStream implements InputStream {
  private $in, $handle;

  /**
   * Creates a new compressing output stream
   *
   * @param  io.streams.InputStream $in The stream to read from
   * @throws lang.IllegalArgumentException
   */
  public function __construct(InputStream $in) {
    $this->in= $in;
    $this->handle= brotli_uncompress_init();

    // There are no magic bytes we can check for, we simply have to try
    // uncompressing, see https://github.com/google/brotli/issues/298
  }

  /**
   * Read a string
   *
   * @param   int limit default 8192
   * @return  string
   */
  public function read($limit= 8192) {
    $bytes= brotli_uncompress_add($this->handle, $this->in->read($limit), BROTLI_PROCESS);
    if ($this->in->available()) {
      $bytes.= brotli_uncompress_add($this->handle, '', BROTLI_FINISH);
    }
    return $bytes;
  }

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   * @return int
   */
  public function available() {
    return $this->in->available();
  }

  /**
   * Close this buffer.
   *
   * @return void
   */
  public function close() {
    if ($this->handle) {

      // The brotli extension does not seem to have a problem when invoking this twice
      brotli_uncompress_add($this->handle, '', BROTLI_FINISH);
      $this->handle= null;
    }
    $this->in->close();
  }
  
  /**
   * Destructor. Ensures output stream is closed.
   */
  public function __destruct() {
    $this->close();
  }
}