<?php namespace lang\unittest;

use lang\reflection\{Method, Property};
use lang\{ClassLoader, ClassNotFoundException, Runnable, Throwable, XPClass, Reflection};
use test\verify\Runtime;
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
  public function property_exists() {
    $class= $this->define([], '{ public $fixture= null; }');
    Assert::instance(Property::class, Reflection::type($class)->property('fixture'));
  }

  #[Test]
  public function method_exists() {
    $class= $this->define([], '{ public function fixture() { } }');
    Assert::instance(Method::class, Reflection::type($class)->method('fixture'));
  }

  #[Test]
  public function parents_property_exists() {
    $class= $this->define(['parent' => Throwable::class]);
    Assert::instance(Property::class, Reflection::type($class)->property('message'));
  }

  #[Test]
  public function parents_method_exists() {
    $class= $this->define(['parent' => Throwable::class]);
    Assert::instance(Method::class, Reflection::type($class)->method('toString'));
  }

  #[Test]
  public function static_initializer_is_invoked() {
    $class= $this->define([], '{
      public static $initializerCalled= false;
      static function __static() { self::$initializerCalled= true; }
    }');
    Assert::true(Reflection::of($class)->property('initializerCalled')->get(null));
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
    Assert::instance(Property::class, Reflection::type($class)->property('fixture'));
  }

  #[Test]
  public function closure_map_style_declaring_method() {
    $class= $this->define([], ['fixture' => function() { }]);
    Assert::instance(Method::class, Reflection::type($class)->method('fixture'));
  }

  #[Test]
  public function closure_map_field_access() {
    $class= $this->define([], ['fixture' => 'Test']);
    Assert::equals('Test', $class->newInstance()->fixture);
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
    Assert::equals('1', $class->newInstance()->fixture(1));
  }

  #[Test]
  public function closure_with_string_return_type() {
    $class= $this->define([], ['fixture' => function($a): string { return $a; }]);
    Assert::equals('1', $class->newInstance()->fixture(1));
  }

  #[Test, Runtime(php: '>=7.1')]
  public function closure_with_void_return_type() {
    $class= $this->define([], ['fixture' => function(): void { }]);
    Assert::null($class->newInstance()->fixture());
  }
}