<?php namespace io\unittest;

use lang\Runnable;

class ChannelWrapper {
  protected static 
    $streams = [];

  protected 
    $offset  = 0,
    $bytes   = '',
    $channel = null;
  
  public
    $context  = null;

  /**
   * Capture output
   *
   * @param   lang.Runnable r
   * @param   array<string, string> initial
   * @return  array<string, string>
   */
  public static function capture(Runnable $r, $initial= []) {
    self::$streams= $initial;
    stream_wrapper_unregister('php');
    stream_wrapper_register('php', self::class);
    
    try {
      $r->run();
    } finally {
      stream_wrapper_restore('php');
    }
    
    return self::$streams;
  }
  
  /**
   * Callback for fopen
   *
   * @param   string path
   * @param   string mode
   * @param   int options
   * @param   string opened_path
   */
  public function stream_open($path, $mode, $options, $opened_path) {
    $channel= substr($path, strlen('php://'));
    if (!isset(self::$streams[$channel])) {
      self::$streams[$channel]= '';
    } else if (strstr($mode, 'w')) {
      $this->offset= strlen(self::$streams[$channel])- 1;
    } else {
      $this->offset= 0;
    }
    $this->channel= $channel;
    return true;
  }

  /**
   * Callback for fclose
   *
   * @return  bool
   */
  public function stream_close() {
    return true;
  }

  /**
   * Stream wrapper method stream_flush
   *
   * @return  bool
   */
  public function stream_flush() {
    return true;
  }

  /**
   * Callback for fwrite
   *
   * @param   string data
   * @return  int length
   */
  public function stream_write($data) {
    $l= strlen($data);
    if ($this->offset < strlen($this->bytes)) {
      self::$streams[$this->channel]= substr_replace(self::$streams[$this->channel], $data, $this->offset, $l);
    } else {
      self::$streams[$this->channel].= $data;
    }
    $this->offset+= $l;
  }
  
  /**
   * Callback for fread
   *
   * @param   int count
   * @return  string
   */
  public function stream_read($count) {
    $chunk= substr(self::$streams[$this->channel], $this->offset, $count);
    $this->offset+= strlen($chunk);
    return $chunk;
  }

  /**
   * Callback for feof
   *
   * @return  bool eof
   */
  public function stream_eof() {
    return $this->offset >= strlen(self::$streams[$this->channel])- 1;
  }

  /**
   * Callback for fseek
   *
   * @param   int offset
   * @param   int whence
   * @return  bool
   */
  public function stream_seek($offset, $whence) {
    switch ($whence) {
      case SEEK_SET: $this->offset= $offset; break;
      case SEEK_CUR: $this->offset+= $offset; break;
      case SEEK_END: $this->offset= strlen(self::$streams[$this->channel]) + $offset; break;
    }
  }

  /**
   * Callback for ftell
   *
   * @return  int position
   */
  public function stream_tell() {
    return $this->offset;
  }
}