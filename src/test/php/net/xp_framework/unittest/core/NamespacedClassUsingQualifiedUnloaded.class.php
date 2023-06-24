<?php namespace net\xp_framework\unittest\core;

class NamespacedClassUsingQualifiedUnloaded {
  
  /** @return net.xp_framework.unittest.core.UnloadedNamespacedClass */
  public function getNamespacedClass() {
    return new UnloadedNamespacedClass();
  }
}