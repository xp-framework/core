<?php namespace io\streams;

use io\FileNotFoundException;
use io\IOException;

/**
 * Wraps I/O streams into PHP streams
 *
 * @test  io.unittest.StreamWrappingTest
 * @see   php://streams
 */
abstract class Streams {
  protected static 
    $streams = [];
  
  public
    $context = null;

  protected 
    $length  = 0,
    $id      = null;
    
  static function __static() {
    stream_wrapper_register('iostrr', get_class(new class() extends Streams {
      static function __static() { }

      public function stream_open($path, $mode, $options, $opened_path) {
        parent::stream_open($path, $mode, $options, $opened_path);
        $this->length= parent::$streams[$this->id]->available();
        return true;
      }

      public function stream_write($data) {
        throw new IOException('Cannot write to readable stream');
      }

      public function stream_read($count) {
        return parent::$streams[$this->id]->read($count);
      }

      public function stream_flush() {
        return true;
      }

      public function stream_eof() {
        return 0 === parent::$streams[$this->id]->available();
      }
    }));
    stream_wrapper_register('iostrw', get_class(new class() extends Streams {
      static function __static() { }

      public function stream_write($data) {
        parent::$streams[$this->id]->write($data);
        $written= strlen($data);
        $this->length+= $written;
        return $written;
      }

      public function stream_truncate($size) {
        if (parent::$streams[$this->id] instanceof Truncation) {
          parent::$streams[$this->id]->truncate($size);
          return true;
        }
        throw new IOException('Cannot truncate underlying stream');
      }

      public function stream_read($count) {
        throw new IOException('Cannot read from writeable stream');
      }

      public function stream_flush() {
        return parent::$streams[$this->id]->flush();
      }

      public function stream_eof() {
        return false;
      }
    }));
  }

  /**
   * Open an input stream for reading
   *
   * @param   io.streams.InputStream s
   * @return  resource
   */
  public static function readableFd(InputStream $s) { 
    $hash= spl_object_hash($s);
    self::$streams[$hash]= $s;
    return fopen('iostrr://'.$hash, 'rb');
  }

  /**
   * Open an input stream for reading and return URI
   *
   * @param   io.streams.InputStream s
   * @return  string
   */
  public static function readableUri(InputStream $s) { 
    $hash= spl_object_hash($s);
    self::$streams[$hash]= $s;
    return 'iostrr://'.$hash;
  }

  /**
   * Open an output stream for writing
   *
   * @param   io.streams.OutputStream s
   * @return  resource
   */
  public static function writeableFd(OutputStream $s) { 
    $hash= spl_object_hash($s);
    self::$streams[$hash]= $s;
    return fopen('iostrw://'.$hash, 'wb');
  }

  /**
   * Open an output stream for writing
   *
   * @param   io.streams.OutputStream s
   * @return  resource
   */
  public static function writeableUri(OutputStream $s) { 
    $hash= spl_object_hash($s);
    self::$streams[$hash]= $s;
    return 'iostrw://'.$hash;
  }
  
  /**
   * Read an IOElements' contents completely into a buffer in a single call.
   *
   * @param   io.streams.InputStream s
   * @return  string
   * @throws  io.IOException
   */
  public static function readAll(InputStream $s) {
    $r= '';
    while ($s->available() > 0) $r.= $s->read();
    return $r;
  }

  /**
   * Callback for fopen
   *
   * @param   string path
   * @param   string mode
   * @param   int options
   * @param   string opened_path
   * @throws  io.FileNotFoundException in case the given file cannot be found
   */
  public function stream_open($path, $mode, $options, $opened_path) {
    sscanf(urldecode($path), "iostr%c://%[^$]", $m, $this->id);
    if (!isset(self::$streams[$this->id])) {
      throw new FileNotFoundException('Cannot open stream "'.$this->id.'" mode '.$mode);
    }
    return true;
  }

  /**
   * Callback for fclose
   *
   * @return  bool
   */
  public function stream_close() {
    if (!isset(self::$streams[$this->id])) return false;

    self::$streams[$this->id]->close();
    unset(self::$streams[$this->id]);
    return true;
  }

  /**
   * Callback for fseek
   *
   * @param   int offset
   * @param   int whence
   * @return  bool
   */
  public function stream_seek($offset, $whence) {
    if (!self::$streams[$this->id] instanceof Seekable) {
      throw new IOException('Underlying stream does not support seeking');
    }

    self::$streams[$this->id]->seek($offset, $whence);
    return true;
  }

  /**
   * Callback for ftell
   *
   * @return  int position
   */
  public function stream_tell() {
    if (!self::$streams[$this->id] instanceof Seekable) {
      throw new IOException('Underlying stream does not support seeking');
    }
    return self::$streams[$this->id]->tell();
  }

  /**
   * Callback for fstat
   *
   * @return  [:var] stat
   */
  public function stream_stat() {
    return ['size' => $this->length];
  }

  /**
   * Callback for stat
   *
   * @see     php://streamwrapper.url-stat
   * @param   string path
   * @param   int flags
   * @return  [:var] stat
   */
  public function url_stat($path, $flags) {
    sscanf(urldecode($path), "iostr%c://%[^$]", $m, $id);
    if (!isset(self::$streams[$id])) {
      return false;
    } else if ('r' === $m) {
      return ['size' => 0, 'mode' => 0100644];
    } else if ('w' === $m) {
      return ['size' => 0, 'mode' => 0100644];
    }
  }

  /**
   * Stream wrapper method stream_flush
   *
   * @return  bool
   */
  public abstract function stream_flush();

  /**
   * Callback for fwrite
   *
   * @param   string data
   * @return  int length
   */
  public abstract function stream_write($data);
  
  /**
   * Callback for fread
   *
   * @param   int count
   * @return  string
   */
  public abstract function stream_read($count);

  /**
   * Callback for feof
   *
   * @return  bool eof
   */
  public abstract function stream_eof();

  /**
   * Callback for casting
   *
   * @param   int cast_as
   * @return  var
   */
  public function stream_cast($cast_as) {
    return false;
  }
}
