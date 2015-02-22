<?php namespace lang\codedom;

trait Decorations {
  protected $modifiers, $annotations;

  /** @return int */
  public function modifiers() { return $this->modifiers; }

  /** @return string */
  public function annotations() { return $this->annotations; }

  /** @param int $modifiers */
  public function access($modifiers) { $this->modifiers= $modifiers; }

  /** @param string $annotations */
  public function annotate($annotations) { $this->annotations= $annotations; }
}