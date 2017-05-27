<?php namespace net\xp_framework\unittest\core;

use lang\ClassLoader;
use unittest\TestCase;

/**
 * Tests the XP Framework's optional namespace support
 */
class NamespaceAliasTest extends TestCase {

  #[@test]
  public function defined_class_exists() {
    ClassLoader::defineClass(
      'net.xp_framework.unittest.core.NamespaceAliasClassFixture', 
      null, 
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
