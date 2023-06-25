<?php namespace lang\unittest;

use lang\ClassLoader;
use unittest\{Assert, Test};

class NamespaceAliasTest {

  #[Test]
  public function defined_class_exists() {
    ClassLoader::defineClass(
      'lang.unittest.NamespaceAliasClassFixture', 
      null, 
      [], 
      '{}'
    );
    Assert::true(class_exists('lang\unittest\NamespaceAliasClassFixture', false));
  }

  #[Test]
  public function defined_interface_exists() {
    ClassLoader::defineInterface(
      'lang.unittest.NamespaceAliasInterfaceFixture', 
      [], 
      '{}'
    );
    Assert::true(interface_exists('lang\unittest\NamespaceAliasInterfaceFixture', false));
  }

  #[Test]
  public function autoloaded_class_exists() {
    new NamespaceAliasAutoloadedFixture();              // Triggers autoloader
    Assert::true(class_exists('lang\unittest\NamespaceAliasAutoloadedFixture', false));
  }

  #[Test]
  public function autoloaded_namespaced_class_exists() {
    new NamespaceAliasAutoloadedNamespacedFixture();    // Triggers autoloader
    Assert::true(class_exists('lang\unittest\NamespaceAliasAutoloadedNamespacedFixture', false));
  }

  #[Test]
  public function autoloaded_fq_class_exists() {
    new NamespaceAliasAutoloadedFQFixture();            // Triggers autoloader
    Assert::true(class_exists('lang\unittest\NamespaceAliasAutoloadedFQFixture', false));
  }
}