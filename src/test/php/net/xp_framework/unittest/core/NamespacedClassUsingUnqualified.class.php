<?php namespace net\xp_framework\unittest\core;

use lang\Object;
  
/**
 * Fixture for namespaces tests
 *
 * @see   xp://net.xp_framework.unittest.core.NamespacedClassesTest
 */
class NamespacedClassUsingUnqualified extends \lang\Object {
  
  /**
   * Returns a new object
   *
   * @return  lang.Object
   */
  public function newObject() {
    return new Object();
  }
}
