<?php namespace util\cmd;

class NoOutput extends \io\streams\StringWriter {
  use NoConsole;

  /**
   * Writes text
   *
   * @param  string $text
   * @return int
   */
  protected function write0($text) { $this->raise(); }
}