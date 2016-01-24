<?php namespace io\streams;

use io\IOException;

/**
 * InputStream that decompresses data compressed using GZIP encoding.
 *
 * @ext   zlib
 * @see   rfc://1952
 * @test  xp://net.xp_framework.unittest.io.streams.GzDecompressingInputStreamTest
 */
class GzDecompressingInputStream implements InputStream {
  private $in, $header;
  public static $wrapped= [];

  static function __static() {
    stream_wrapper_register('zlib.bounded', get_class(newinstance('lang.Object', [], '{
      protected $id, $st= null;
      protected $buffer= "";
      public $context = null;
      
      public function stream_open($path, $mode, $options, $opened_path) {
        $this->st= \io\streams\GzDecompressingInputStream::$wrapped[$path];
        $this->id= $path;
        return true;
      }

      public function stream_read($count) {

        // Ensure we have at least 9 bytes
        $l= strlen($this->buffer);
        while ($l < 9 && $this->st->available() > 0) {
          $chunk= $this->st->read($count);
          $l+= strlen($chunk);
          $this->buffer.= $chunk;
        }
        
        // Now return the everything except the last 8 bytes
        $read= substr($this->buffer, 0, -8);
        $this->buffer= substr($this->buffer, -8);
        return $read;
      }

      public function stream_eof() {
        return 0 === $this->st->available();
      }

      public function stream_flush() {
        return true;
      }
      
      public function stream_close() {
        $this->st->close();
        unset(\io\streams\GzDecompressingInputStream::$wrapped[$this->id]);
      }
    }')));
  }
  
  /**
   * Constructor
   *
   * @param   io.streams.InputStream $in
   * @throws  io.IOException
   */
  public function __construct(InputStream $in) {
    
    // Read GZIP format header
    // * ID1, ID2 (Identification, \x1F, \x8B)
    // * CM       (Compression Method, 8 = deflate)
    // * FLG      (Flags)
    // * MTIME    (Modification time, Un*x timestamp)
    // * XFL      (Extra flags)
    // * OS       (Operating system)
    $this->header= unpack('a2id/Cmethod/Cflags/Vtime/Cextra/Cos', $in->read(10));
    if ("\x1F\x8B" != $this->header['id']) {
      throw new IOException('Invalid format, expected \037\213, have '.addcslashes($this->header['id'], "\0..\377"));
    }
    if (8 !== $this->header['method']) {
      throw new IOException('Unknown compression method #'.$this->header['method']);
    }
    if (8 === ($this->header['flags'] & 8)) {
      $this->header['filename']= '';
      while ("\x00" !== ($b= $in->read(1))) {
        $this->header['filename'].= $b;
      }
    }

    // Now, convert stream to file handle and append inflating filter
    $wri= 'zlib.bounded://'.spl_object_hash($in);
    self::$wrapped[$wri]= $in;
    $this->in= fopen($wri, 'r');
    if (!stream_filter_append($this->in, 'zlib.inflate', STREAM_FILTER_READ)) {
      throw new IOException('Could not append stream filter');
    }
  }

  /** @return [:var] */
  public function header() { return $this->header; }

  /**
   * Read a string
   *
   * @param   int limit default 8192
   * @return  string
   */
  public function read($limit= 8192) {
    return fread($this->in, $limit);
  }

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   */
  public function available() {
    return feof($this->in) ? 0 : 1;
  }

  /**
   * Close this buffer.
   *
   */
  public function close() {
    if (!$this->in) return;
    fclose($this->in);
    $this->in= null;
  }
  
  /**
   * Destructor. Ensures output stream is closed.
   *
   */
  public function __destruct() {
    $this->close();
  }
}
