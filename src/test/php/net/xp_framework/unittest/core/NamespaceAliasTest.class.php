<?php namespace net\xp_framework\unittest\core;

use lang\Object;
use lang\ClassLoader;
use lang\Generic;
use unittest\TestCase;

/**
 * Tests the XP Framework's optional namespace support
 */
class NamespaceAliasTest extends TestCase {

  #[@test]
  public function lang_object_class_exists() {
    $this->assertTrue(class_exists(Object::class, false));
  }

  #[@test]
  public function lang_generic_interface_exists() {
    $this->assertTrue(interface_exists(Generic::class, false));
  }

  #[@test]
  public function unittest_testcase_class_exists() {
    $this->assertTrue(class_exists(TestCase::class, false));
  }

  #[@test]
  public function defined_class_exists() {
    ClassLoader::defineClass(
      'net.xp_framework.unittest.core.NamespaceAliasClassFixture', 
      Object::class, 
      [], 
      '{}'
    );
    $this->assertTrue(class_exists('net\xp_framework\unittest\core\NamespaceAliasClassFixture', false));
  }

  #[@test]
  public function defined_interface_exists() {
    ClassLoader::defineInterface(
      'net.xp_framework.unittest.core.NamespaceAliasInterfaceFixture', 
      [], 
      '{}'
    );
    $this->assertTrue(interface_exists('net\xp_framework\unittest\core\NamespaceAliasInterfaceFixture', false));
  }

  #[@test]
  public function autoloaded_class_exists() {
    new NamespaceAliasAutoloadedFixture();              // Triggers autoloader
    $this->assertTrue(class_exists('net\xp_framework\unittest\core\NamespaceAliasAutoloadedFixture', false));
  }

  #[@test]
  public function autoloaded_namespaced_class_exists() {
    new NamespaceAliasAutoloadedNamespacedFixture();    // Triggers autoloader
    $this->assertTrue(class_exists('net\xp_framework\unittest\core\NamespaceAliasAutoloadedNamespacedFixture', false));
  }

  #[@test]
  public function autoloaded_fq_class_exists() {
    new NamespaceAliasAutoloadedFQFixture();            // Triggers autoloader
    $this->assertTrue(class_exists('net\xp_framework\unittest\core\NamespaceAliasAutoloadedFQFixture', false));
  }
}
