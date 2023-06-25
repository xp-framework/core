<?php namespace lang\unittest;

class NamespacedClassUsingQualifiedUnloaded {
  
  /** @return lang.unittest.UnloadedNamespacedClass */
  public function getNamespacedClass() {
    return new UnloadedNamespacedClass();
  }
}