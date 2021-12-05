<?php namespace xp\runtime;

use lang\XPException;

class CouldNotLoadDependencies extends XPException {
  private $modules;

  /** Creates a new instance */
  public function __construct(array $modules) {
    parent::__construct('Could not load modules '.implode(', ', $modules));
    $this->modules= $modules;
  }

  /** Returns modules */
  public function modules(): array { return $this->modules; }
}