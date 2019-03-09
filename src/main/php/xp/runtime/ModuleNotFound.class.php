<?php namespace xp\runtime;

use lang\XPException;

class ModuleNotFound extends XPException {
  private $module;

  /** Creates a new instance */
  public function __construct(string $module) {
    parent::__construct('Could not find module '.$module);
    $this->module= $module;
  }

  /** Returns module */
  public function module(): string { return $this->module; }
}