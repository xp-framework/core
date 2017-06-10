<?php namespace util\cmd;

class NoInput extends \io\streams\StringReader {
  use NoConsole;

  /**
   * Read a string
   *
   * @param  int $limit default 8192
   * @return string
   */
  public function read($limit= 8192) { $this->raise(); }

  /**
   * Read a string
   *
   * @param  int $limit default 8192
   * @return string
   */
  public function readLine() { $this->raise(); }
}