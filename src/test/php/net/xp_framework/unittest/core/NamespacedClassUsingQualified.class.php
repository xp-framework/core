<?php namespace net\xp_framework\unittest\core;

/**
 * Fixture for namespaces tests
 *
 * @see   xp://net.xp_framework.unittest.core.NamespacedClassesTest
 */
class NamespacedClassUsingQualified {
  
  /**
   * Returns a namespaced class
   *
   * @return  net.xp_framework.unittest.core.NamespacedClass
   */
  public function getNamespacedClass() {
    return new NamespacedClass();
  }
}
