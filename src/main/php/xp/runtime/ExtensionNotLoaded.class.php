<?php namespace xp\runtime;

use lang\XPException;

class ExtensionNotLoaded extends XPException {
  private $extension;

  /** Creates a new instance */
  public function __construct(string $extension) {
    parent::__construct('PHP extension '.$extension.' not loaded');
    $this->extension= $extension;
  }

  /** Returns extension */
  public function extension(): string { return $this->extension; }
}