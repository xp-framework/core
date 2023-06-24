<?php namespace net\xp_framework\unittest\reflection;

use lang\{ClassLoader, ClassNotFoundException, Closeable, Runnable, XPClass};
use unittest\Assert;
use unittest\{Expect, Test};

/**
 * TestCase for lang.ClassLoader::defineInterface()
 */
class RuntimeInterfaceDefinitionTest extends RuntimeTypeDefinitionTest {

  /**
   * This `define()` implementation creates interfaces
   *
   * @param   [:var] $decl
   * @param   var $def
   * @return  lang.XPClass
   */
  protected function define(array $decl= [], $def= null) {
    return $this->defineType(
      array_key_exists('annotations', $decl) ? $decl['annotations'] : '',
      array_key_exists('name', $decl) ? $decl['name'] : '',
      function($spec) use($decl, $def) {
        return ClassLoader::defineInterface(
          $spec,
          array_key_exists('parents', $decl) ? $decl['parents'] : [],
          $def
        );
      }
    );
  }

  #[Test]
  public function given_parent_is_inherited() {
    Assert::equals(
      [XPClass::forName(Runnable::class)],
      $this->define(['parents' => [Runnable::class]])->getInterfaces()
    );
  }

  #[Test]
  public function given_parent_class_is_inherited() {
    Assert::equals(
      [XPClass::forName(Runnable::class)],
      $this->define(['parents' => [XPClass::forName(Runnable::class)]])->getInterfaces()
    );
  }

  #[Test]
  public function given_parents_are_inherited() {
    Assert::equals(
      [XPClass::forName(Runnable::class), XPClass::forName(Closeable::class)],
      $this->define(['parents' => [Runnable::class, Closeable::class]])->getInterfaces()
    );
  }

  #[Test]
  public function given_parent_classes_are_inherited() {
    Assert::equals(
      [XPClass::forName(Runnable::class), XPClass::forName(Closeable::class)],
      $this->define(['parents' => [XPClass::forName(Runnable::class), XPClass::forName(Closeable::class)]])->getInterfaces()
    );
  }

  #[Test]
  public function parents_method_exists() {
    Assert::true($this->define(['parents' => [Runnable::class]])->hasMethod('run'));
  }

  #[Test]
  public function method_exists() {
    $class= $this->define(['parents' => [Runnable::class]], '{ public function runAs($user); }');
    Assert::true($class->hasMethod('runAs'));
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function cannot_define_interface_with_non_existant_parent() {
    $this->define(['parents' => ['@@nonexistant@@']]);
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function cannot_define_interface_with_null_parent() {
    $this->define(['parents' => [null]]);
  }

  #[Test]
  public function closure_map_style_declaring_method() {
    $class= $this->define(['parents' => [Runnable::class]], ['fixture' => function() { }]);
    Assert::true($class->hasMethod('fixture'));
  }
}