<?php namespace io\streams;

/**
 * Reads strings from an underlying input stream. In contrast to the
 * TextReader, no character set conversio is performed.
 *
 * @test  xp://net.xp_framework.unittest.io.streams.StringReaderTest
 */
class StringReader extends Reader {

  /**
   * Read a number of bytes. Returns NULL if no more data is available.
   *
   * @param   int size default 8192
   * @return  string
   */
  public function read($size= 8192) {
    if (0 === $size) return '';

    $this->beginning= false;
    $bytes= $this->read0($size);
    if ('' === $bytes) {
      $this->buf= null;
      return null;
    } else {
      return $bytes;
    }
  }
  
  /**
   * Read an entire line
   *
   * @return  string NULL when end of data is reached
   */
  public function readLine() {
    if (null === $this->buf) return null;

    $this->beginning= false;
    do {
      $p= strcspn($this->buf, "\r\n");
      $l= strlen($this->buf);
      if ($p >= $l) {
        $chunk= $this->stream->read();
        if ('' === $chunk) {
          if ('' === $this->buf) return null;
          $bytes= $p === $l ? $this->buf : substr($this->buf, 0, $p);
          $this->buf= null;
          break;
        }
        $this->buf.= $chunk;
        continue;
      }

      $o= "\r" === $this->buf[$p] && $p < $l - 1 && "\n" === $this->buf[$p + 1] ? 2 : 1;
      $bytes= substr($this->buf, 0, $p);
      $this->buf= substr($this->buf, $p + $o);
      break;
    } while (true);

    return $bytes;
  }
}
