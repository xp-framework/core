<?php namespace lang\unittest;

use lang\{ClassLoader, ClassNotFoundException, Closeable, Runnable, XPClass};
use test\{Assert, Expect, Test};

class RuntimeInterfaceDefinitionTest extends RuntimeTypeDefinitionTest {

  /**
   * This `define()` implementation creates interfaces
   *
   * @param  [:var] $decl
   * @param  var $def
   * @return lang.XPClass
   */
  protected function define(array $decl= [], $def= null) {
    return $this->defineType(
      $decl['annotations']  ?? '',
      $decl['name'] ?? '',
      fn($spec) => ClassLoader::defineInterface($spec, $decl['parents'] ?? [], $def)
    );
  }

  /** Yields interfaces a given class implements */
  private function interfacesOf(XPClass $class): iterable {
    foreach ($class->reflect()->getInterfaces() as $interface) {
      yield new XPClass($interface);
    }
  }

  #[Test]
  public function given_parent_is_inherited() {
    Assert::equals(
      [XPClass::forName(Runnable::class)],
      [...$this->interfacesOf($this->define(['parents' => [Runnable::class]]))]
    );
  }

  #[Test]
  public function given_parent_class_is_inherited() {
    Assert::equals(
      [XPClass::forName(Runnable::class)],
      [...$this->interfacesOf($this->define(['parents' => [XPClass::forName(Runnable::class)]]))]
    );
  }

  #[Test]
  public function given_parents_are_inherited() {
    Assert::equals(
      [XPClass::forName(Runnable::class), XPClass::forName(Closeable::class)],
      [...$this->interfacesOf($this->define(['parents' => [Runnable::class, Closeable::class]]))]
    );
  }

  #[Test]
  public function given_parent_classes_are_inherited() {
    Assert::equals(
      [XPClass::forName(Runnable::class), XPClass::forName(Closeable::class)],
      [...$this->interfacesOf($this->define(['parents' => [XPClass::forName(Runnable::class), XPClass::forName(Closeable::class)]]))]
    );
  }

  #[Test]
  public function parents_method_exists() {
    $class= $this->define(['parents' => [Runnable::class]]);
    Assert::true(method_exists($class->literal(), 'run'));
  }

  #[Test]
  public function method_exists() {
    $class= $this->define(['parents' => [Runnable::class]], '{ public function runAs($user); }');
    Assert::true(method_exists($class->literal(), 'runAs'));
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
    Assert::true(method_exists($class->literal(), 'fixture'));
  }
}