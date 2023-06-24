<?php namespace net\xp_framework\unittest\core;
/**
 * Fixture for namespaces tests
 *
 * @see   xp://net.xp_framework.unittest.core.NamespacedClassesTest
 */
class NamespacedClassUsingQualifiedUnloaded {
  
  /**
   * Returns a namespaced class
   *
   * @return  net.xp_framework.unittest.core.UnloadedNamespacedClass
   */
  public function getNamespacedClass() {
    return new UnloadedNamespacedClass();
  }
}