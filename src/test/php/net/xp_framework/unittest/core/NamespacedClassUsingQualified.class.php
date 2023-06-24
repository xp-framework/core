<?php namespace net\xp_framework\unittest\core;

class NamespacedClassUsingQualified {
  
  /** @return net.xp_framework.unittest.core.NamespacedClass */
  public function getNamespacedClass() {
    return new NamespacedClass();
  }
}