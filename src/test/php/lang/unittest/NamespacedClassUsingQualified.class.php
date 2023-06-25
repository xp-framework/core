<?php namespace lang\unittest;

class NamespacedClassUsingQualified {
  
  /** @return lang.unittest.NamespacedClass */
  public function getNamespacedClass() {
    return new NamespacedClass();
  }
}