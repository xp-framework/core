<?php namespace lang\unittest;

class NamespacedClassUsingUnqualified {
  
  /** Returns a new name */
  public function newName(): Name { return new Name('Test'); }
}