<?php namespace xp\runtime;

use lang\XPException;

class CouldNotLoadDependencies extends XPException {
  private $errors;

  /** Creates a new instance */
  public function __construct(array $errors) {
    parent::__construct('Could not load modules '.implode(', ', array_keys($errors)));
    $this->errors= $errors;
  }

  /** Returns module modules */
  public function modules(): array { return array_keys($this->errors); }
}