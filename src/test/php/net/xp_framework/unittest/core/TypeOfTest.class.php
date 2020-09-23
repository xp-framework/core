<?php namespace net\xp_framework\unittest\core;

use lang\{ArrayType, FunctionType, MapType, Primitive, Type, XPClass};
use unittest\actions\RuntimeVersion;
use unittest\{Action, Test};

/**
 * Tests typeof() functionality
 */
class TypeOfTest extends \unittest\TestCase {

  #[Test]
  public function null() {
    $this->assertEquals(Type::$VOID, typeof(null));
  }

  #[Test]
  public function this() {
    $this->assertEquals(new XPClass(self::class), typeof($this));
  }

  #[Test]
  public function native() {
    $this->assertEquals(new XPClass(\ArrayObject::class), typeof(new \ArrayObject([])));
  }

  #[Test]
  public function string() {
    $this->assertEquals(Primitive::$STRING, typeof($this->name));
  }

  #[Test]
  public function intArray() {
    $this->assertEquals(ArrayType::forName('var[]'), typeof([1, 2, 3]));
  }

  #[Test]
  public function intMap() {
    $this->assertEquals(MapType::forName('[:var]'), typeof(['one' => 1, 'two' => 2, 'three' => 3]));
  }

  #[Test]
  public function function_without_arg() {
    $this->assertEquals(FunctionType::forName('function(): var'), typeof(function() { }));
  }

  #[Test]
  public function function_with_arg() {
    $this->assertEquals(FunctionType::forName('function(var): var'), typeof(function($a) { }));
  }

  #[Test]
  public function function_with_args() {
    $this->assertEquals(FunctionType::forName('function(var, var): var'), typeof(function($a, $b) { }));
  }

  #[Test]
  public function function_with_var_arg() {
    $this->assertEquals(FunctionType::forName('function(): var'), typeof(function(... $a) { }));
  }

  #[Test]
  public function function_with_normal_and_var_arg() {
    $this->assertEquals(FunctionType::forName('function(lang.Type): var'), typeof(function(Type $t, ... $a) { }));
  }

  #[Test, Action(new RuntimeVersion('>=7.0'))]
  public function function_with_typed_var_arg() {
    $this->assertEquals(FunctionType::forName('function(): var'), typeof(eval('return function(\lang\Type... $a) { };')));
  }

  #[Test]
  public function function_with_class_hint() {
    $this->assertEquals(FunctionType::forName('function(lang.Type): var'), typeof(function(Type $t) { }));
  }

  #[Test]
  public function function_with_array_hint() {
    $this->assertEquals(new FunctionType([Type::$ARRAY], Type::$VAR), typeof(function(array $a) { }));
  }

  #[Test]
  public function function_with_callable_hint() {
    $this->assertEquals(new FunctionType([Type::$CALLABLE], Type::$VAR), typeof(function(callable $c) { }));
  }

  #[Test, Action(new RuntimeVersion('>=7.0'))]
  public function function_with_primitive_arg() {
    $this->assertEquals(FunctionType::forName('function(int): var'), typeof(eval('return function(int $a) { };')));
  }

  #[Test, Action(new RuntimeVersion('>=7.0'))]
  public function function_with_return_type() {
    $this->assertEquals(FunctionType::forName('function(): lang.Type'), typeof(eval('return function(): \lang\Type { };')));
  }

  #[Test, Action(new RuntimeVersion('>=7.0'))]
  public function function_with_primitive_return_type() {
    $this->assertEquals(FunctionType::forName('function(): int'), typeof(eval('return function(): int { };')));
  }

  #[Test, Action(new RuntimeVersion('>=7.1'))]
  public function function_with_void_return_type() {
    $this->assertEquals(FunctionType::forName('function(): void'), typeof(eval('return function(): void { };')));
  }
}