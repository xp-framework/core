<?php namespace lang\unittest;

trait Listing {
  private $elements;

  /** @param var[] $initial */
  public function __construct(array $initial) { $this->elements= $initial; }

  /** @return var[] */
  public function elements() { return $this->elements; }
}