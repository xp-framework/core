<?php namespace lang\unittest;

use net\xp_framework\unittest\Name;
  
class NamespacedClassUsingUnqualified {
  
  /** Returns a new name */
  public function newName(): Name {
    return new Name('Test');
  }
}