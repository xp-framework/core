<?php namespace xp\runtime;

class Script {
  public static $code= [];
  private $stream, $offset;
  public $context;

  /**
   * Opens path
   *
   * @param  string $path
   * @param  string $mode
   * @param  int $options
   * @param  string $opened
   */
  public function stream_open($path, $mode, $options, &$opened) {
    sscanf($path, "%[^:]://%[^\r]", $scheme, $opened);
    if (isset(self::$code[$opened])) {
      $this->stream= self::$code[$opened];
      $this->offset= 0;
      return true;
    }

    return false;
  }

  /**
   * Reads bytes
   *
   * @param  int $count
   * @return string
   */
  public function stream_read($count) {
    $chunk= substr($this->stream, $this->offset, $count);
    $this->offset+= $count;
    return $chunk;
  }

  /** @return [:var] */
  public function stream_stat() {
    return ['size' => strlen($this->stream)];
  }

  /** @return bool */
  public function stream_eof() {
    return $this->offset >= strlen($this->stream);
  }

  /** @return void */
  public function stream_close() {
    // NOOP
  }

  /** @return void */
  public function stream_set_option($option, $arg1, $arg2) {
    // NOOP
  }
}