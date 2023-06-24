<?php namespace net\xp_framework\unittest\core;

use lang\ClassLoader;
use unittest\Assert;
use unittest\{Test, TestCase};

/**
 * Tests the XP Framework's optional namespace support
 */
class NamespaceAliasTest {

  #[Test]
  public function defined_class_exists() {
    ClassLoader::defineClass(
      'net.xp_framework.unittest.core.NamespaceAliasClassFixture', 
      null, 
      [], 
      '{}'
    );
    Assert::true(class_exists('net\xp_framework\unittest\core\NamespaceAliasClassFixture', false));
  }

  #[Test]
  public function defined_interface_exists() {
    ClassLoader::defineInterface(
      'net.xp_framework.unittest.core.NamespaceAliasInterfaceFixture', 
      [], 
      '{}'
    );
    Assert::true(interface_exists('net\xp_framework\unittest\core\NamespaceAliasInterfaceFixture', false));
  }

  #[Test]
  public function autoloaded_class_exists() {
    new NamespaceAliasAutoloadedFixture();              // Triggers autoloader
    Assert::true(class_exists('net\xp_framework\unittest\core\NamespaceAliasAutoloadedFixture', false));
  }

  #[Test]
  public function autoloaded_namespaced_class_exists() {
    new NamespaceAliasAutoloadedNamespacedFixture();    // Triggers autoloader
    Assert::true(class_exists('net\xp_framework\unittest\core\NamespaceAliasAutoloadedNamespacedFixture', false));
  }

  #[Test]
  public function autoloaded_fq_class_exists() {
    new NamespaceAliasAutoloadedFQFixture();            // Triggers autoloader
    Assert::true(class_exists('net\xp_framework\unittest\core\NamespaceAliasAutoloadedFQFixture', false));
  }
}