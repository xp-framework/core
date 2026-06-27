<?php namespace lang\unittest;

use lang\{ClassLoader, ClassNotFoundException, Runnable, Throwable, XPClass};
use test\{Action, Assert, Expect, Test, Values};

class RuntimeClassDefinitionTest extends RuntimeTypeDefinitionTest {

  /**
   * This `define()` implementation creates classes
   *
   * @param  [:var] $decl
   * @param  var $def
   * @return lang.XPClass
   */
  protected function define(array $decl= [], $def= null) {
    return $this->defineType(
      array_key_exists('annotations', $decl) ? $decl['annotations'] : '',
      array_key_exists('name', $decl) ? $decl['name'] : '',
      function($spec) use($decl, $def) {
        return ClassLoader::defineClass(
          $spec,
          array_key_exists('parent', $decl) ? $decl['parent'] : null,
          array_key_exists('interfaces', $decl) ? $decl['interfaces'] : [],
          $def
        );
      }
    );
  }

  #[Test]
  public function given_parent_is_inherited() {
    Assert::true($this->define(['parent' => Throwable::class])->isSubclassOf(Throwable::class));
  }

  #[Test]
  public function given_parent_class_is_inherited() {
    Assert::true($this->define(['parent' => XPClass::forName(Throwable::class)])->isSubclassOf(Throwable::class));
  }

  #[Test]
  public function given_interface_is_implemented() {
    $class= $this->define(['interfaces' => [Runnable::class]], '{
      public function run() { } 
    }');

    Assert::true($class->isSubclassOf(Runnable::class));
  }

  #[Test]
  public function given_interface_class_is_implemented() {
    $class= $this->define(['interfaces' => [XPClass::forName(Runnable::class)]], '{
      public function run() { } 
    }');

    Assert::true($class->isSubclassOf(Runnable::class));
  }

  #[Test]
  public function field_exists() {
    $class= $this->define([], '{ public $fixture= null; }');
    Assert::true(property_exists($class->literal(), 'fixture'));
  }

  #[Test]
  public function method_exists() {
    $class= $this->define([], '{ public function fixture() { } }');
    Assert::true(method_exists($class->literal(), 'fixture'));
  }

  #[Test]
  public function parents_method_exists() {
    $class= $this->define(['parent' => Throwable::class]);
    Assert::true(method_exists($class->literal(), 'toString'));
  }

  #[Test]
  public function parents_field_exists() {
    $class= $this->define(['parent' => Throwable::class]);
    Assert::true(property_exists($class->literal(), 'message'));
  }

  #[Test]
  public function static_initializer_is_invoked() {
    $class= $this->define([], '{
      public static $initializerCalled= false;
      static function __static() { self::$initializerCalled= true; }
    }');
    Assert::true(eval("return {$class->literal()}::\$initializerCalled;"));
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function cannot_define_class_with_non_existant_parent() {
    $this->define(['parent' => '@@nonexistant@@']);
  }

  #[Test, Expect(ClassNotFoundException::class), Values([[['@@nonexistant@@']], [[Runnable::class, '@@nonexistant@@']], [['@@nonexistant@@', Runnable::class]]])]
  public function cannot_define_class_with_non_existant_interface($list) {
    $this->define(['interfaces' => $list]);
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function cannot_define_class_with_null_interface() {
    $this->define(['interfaces' => [null]]);
  }

  #[Test]
  public function closure_map_style_declaring_field() {
    $class= $this->define([], ['fixture' => null]);
    Assert::true(property_exists($class->literal(), 'fixture'));
  }

  #[Test]
  public function closure_map_style_declaring_method() {
    $class= $this->define([], ['fixture' => function() { }]);
    Assert::true(method_exists($class->literal(), 'fixture'));
  }

  #[Test]
  public function closure_map_field_access() {
    $class= $this->define([], ['fixture' => 'Test']);
    $instance= $class->newInstance();
    Assert::equals('Test', $instance->fixture);
  }

  #[Test]
  public function closure_map_method_invocation() {
    $class= $this->define([], ['fixture' => function($a, $b) { return [$this, $a, $b]; }]);
    $instance= $class->newInstance();
    Assert::equals([$instance, 1, 2], $instance->fixture(1, 2));
  }

  #[Test]
  public function closure_with_string_parameter_type() {
    $class= $this->define([], ['fixture' => function(string $a) { return $a; }]);
    $instance= $class->newInstance();
    Assert::equals('1', $instance->fixture(1));
  }

  #[Test]
  public function closure_with_string_return_type() {
    $class= $this->define([], ['fixture' => function($a): string { return $a; }]);
    $instance= $class->newInstance();
    Assert::equals('1', $instance->fixture(1));
  }

  #[Test]
  public function closure_with_void_return_type() {
    $class= $this->define([], ['fixture' => function($a): void { }]);
    $instance= $class->newInstance();
    Assert::null($instance->fixture(1));
  }
}