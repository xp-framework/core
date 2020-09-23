<?php namespace net\xp_framework\unittest\core;

use net\xp_framework\unittest\Name;
  
/**
 * Fixture for namespaces tests
 *
 * @see   xp://net.xp_framework.unittest.core.NamespacedClassesTest
 */
class NamespacedClassUsingUnqualified {
  
  /** Returns a new name */
  public function newName(): Name {
    return new Name('Test');
  }
}