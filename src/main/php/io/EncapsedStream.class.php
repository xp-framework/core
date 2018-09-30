<?php namespace io;

use lang\MethodNotImplementedException;

/**
 * Encapsulated / embedded stream
 *
 * @test  xp://net.xp_framework.unittest.io.EncapsedStreamTest
 */
class EncapsedStream extends File {
  private $_super, $_offset, $_size;
  public $offset;

  /**
   * Constructor
   *
   * @param   io.File super parent stream
   * @param   int offset offset where encapsed stream starts in parent stream
   * @param   int size
   * @throws  lang.IllegalStateException when stream is not yet opened
   */
  public function __construct($super, $offset, $size) {
    if (!$super->isOpen()) throw new \lang\IllegalStateException(
      'Super-stream must be opened in EncapsedStream'
    );
    
    $this->_super= $super;
    $this->_offset= $offset;
    $this->_size= $size;
  }
  
  /**
   * Prepares the stream for the next operation (eg. moves the
   * pointer to the correct position).
   *
   * @return bool
   */
  protected function _prepare() {
    if ($this->offset < $this->_size) {
      $this->_super->seek($this->_offset + $this->offset);
      return true;
    } else {
      return false;
    }
  }
  
  /**
   * Open the stream. For EncapsedStream only reading is supported
   *
   * @param   string mode default File::READ one of the File::* constants
   * @return  self
   */
  public function open($mode= File::READ): parent {
    if (File::READ !== $mode) throw new \lang\IllegalAccessException(
      'EncapsedStream only supports reading but writing operation requested.'
    );
    return $this;
  }
  
  /**
   * Returns whether this stream is open
   *
   * @return  bool TRUE, when the stream is open
   */
  public function isOpen(): bool {
    return $this->_super->isOpen();
  }
  
  /**
   * Retrieve the stream's size in bytes
   *
   * @return  int size streamsize in bytes
   */
  public function size(): int {
    return $this->_size;
  }
  
  /**
   * Truncate the stream to the specified length
   *
   * @param   int size default 0
   * @return  bool
   */
  public function truncate(int $size= 0): bool {
    throw new MethodNotImplementedException('Truncation not supported');
  }
  
  /**
   * Read one line and chop off trailing CR and LF characters
   *
   * Returns a string of up to length - 1 bytes read from the stream. 
   * Reading ends when length - 1 bytes have been read, on a newline (which is 
   * included in the return value), or on EOF (whichever comes first). 
   *
   * @param   int bytes default 4096 Max. ammount of bytes to be read
   * @return  string Data read
   */
  public function readLine($bytes= 4096) {
    if ($this->_prepare()) {
      $bytes= $this->_super->gets(min($bytes, $this->_size - $this->offset + 1));
      $this->offset+= strlen($bytes);
      return false === $bytes ?: chop($bytes);
    } else {
      return false;
    }
  }
  
  /**
   * Read one char
   *
   * @return  string the character read
   */
  public function readChar() {
    if ($this->_prepare()) {
      $c= $this->_super->readChar();
      $this->offset+= strlen($c);
      return $c;
    } else {
      return false;
    }
  }
  
  /**
   * Read a line
   *
   * This function is identical to readLine except that trailing CR and LF characters
   * will be included in its return value
   *
   * @param   int bytes default 4096 Max. ammount of bytes to be read
   * @return  string Data read
   */
  public function gets($bytes= 4096) {
    if ($this->_prepare()) {
      $bytes= $this->_super->gets(min($bytes, $this->_size - $this->offset + 1));
      $this->offset+= strlen($bytes);
      return $bytes;
    } else {
      return false;
    }
  }
  
  /**
   * Read (binary-safe)
   *
   * @param   int bytes default 4096 Max. ammount of bytes to be read
   * @return  string Data read
   */
  public function read($bytes= 4096) {
    if ($this->_prepare()) {
      $bytes= $this->_super->read(min($bytes, $this->_size - $this->offset));
      $this->offset+= strlen($bytes);
      return $bytes;
    } else {
      return false;
    }
  }
  
  /**
   * Write. No supported in EncapsedStream
   *
   * @param   string string data to write
   * @return  int number of bytes written
   */
  public function write($string) {
    throw new MethodNotImplementedException('Writing not supported');
  }    

  /**
   * Write a line and append a LF (\n) character. Not supported in EncapsedStream
   *
   * @param   string string default '' data to write
   * @return  int number of bytes written
   */
  public function writeLine($string= '') {
    throw new MethodNotImplementedException('Writing not supported');
  }
  
  /**
   * Returns whether the stream pointer is at the end of the stream
   *
   * @return  bool TRUE when the end of the stream is reached
   */
  public function eof() {
    return $this->offset >= $this->_size;
  }
  
  /**
   * Move stream pointer to a new position. If the pointer exceeds the
   * actual buffer size, it is reset to the end of the buffer. This case
   * is not considered an error.
   *
   * @see     php://fseek
   * @param   int position default 0 The new position
   * @param   int mode default SEEK_SET 
   * @return  bool success
   */
  public function seek($position= 0, $mode= SEEK_SET) {
    switch ($mode) {
      case SEEK_SET: $this->offset= min($this->_size, $position); break;
      case SEEK_CUR: $this->offset= min($this->_size, $this->offset+ $position); break;
      case SEEK_END: $this->offset= $this->_size; break;
    }
    
    return true;
  }

  /**
   * Retrieve stream pointer position
   *
   * @return  int position
   */
  public function tell() {
    return $this->offset;
  }


  /**
   * Close this stream
   *
   * @return  bool success
   */
  public function close() {
    return true;
  }
}
