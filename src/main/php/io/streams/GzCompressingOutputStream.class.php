<?php namespace io\streams;

/**
 * OuputStream that compresses content using GZIP encoding. Data
 * produced with this stream can be read with the "gunzip" command
 * line utility and its "zcat", "zgrep", ... list of friends.
 *
 * @ext   zlib
 * @see   rfc://1952
 * @test  xp://net.xp_framework.unittest.io.streams.GzCompressingOutputStreamTest
 */
class GzCompressingOutputStream implements OutputStream {
  protected $out= null;
  protected $md= null;
  protected $length= null;
  protected $filter= null;
  
  /**
   * Constructor
   *
   * @param   io.streams.OutputStream out
   * @param   int level default 6
   * @throws  lang.IllegalArgumentException if the level is not between 0 and 9
   */
  public function __construct(OutputStream $out, $level= 6) {
    if ($level < 0 || $level > 9) {
      throw new \lang\IllegalArgumentException('Level '.$level.' out of range [0..9]');
    }

    // Write GZIP format header:
    // * ID1, ID2 (Identification, \x1F, \x8B)
    // * CM       (Compression Method, 8 = deflate)
    // * FLG      (Flags, use 0)
    // * MTIME    (Modification time, Un*x timestamp)
    // * XFL      (Extra flags, 2 = compressor used maximum compression)
    // * OS       (Operating system, 255 = unknown)
    $out->write(pack('CCCCVCC', 0x1F, 0x8B, 8, 0, time(), 2, 255));
    
    // Now, convert stream to file handle and append deflating filter
    $this->out= Streams::writeableFd($out);
    if (!($this->filter= stream_filter_append($this->out, 'zlib.deflate', STREAM_FILTER_WRITE, $level))) {
      fclose($this->out);
      $this->out= null;
      throw new \io\IOException('Could not append stream filter');
    }
    $this->md= hash_init('crc32b');
  }
  
  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) {
    fwrite($this->out, $arg);
    $this->length+= strlen($arg);
    hash_update($this->md, $arg);
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
    if (!$this->out) return;
  
    // Remove deflating filter so we can continue writing "raw"
    stream_filter_remove($this->filter);

    $final= hash_final($this->md, true);
    
    // Write GZIP footer:
    // * CRC32    (CRC-32 checksum)
    // * ISIZE    (Input size)
    fwrite($this->out, pack('aaaaV', $final{3}, $final{2}, $final{1}, $final{0}, $this->length));
    fclose($this->out);
    $this->out= null;
    $this->md= null;
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

  /** @return string */
  public function toString() {
    return nameof($this).'(->'.$this->out.')';
  }
}
