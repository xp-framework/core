<?php namespace xp\runtime;

use lang\ElementNotFoundException;

class ModuleNotFound extends ElementNotFoundException {
  private $module;

  /** Creates a new instance */
  public function __construct(string $module, string $import= null) {
    parent::__construct('Could not load module '.$module.($import ? ' used to import '.$import : ''));
    $this->module= $module;
  }

  /** Returns module name */
  public function module(): string { return $this->module; }
}