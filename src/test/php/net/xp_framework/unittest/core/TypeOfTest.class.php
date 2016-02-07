<?php namespace net\xp_framework\unittest\core;

use lang\Primitive;
use lang\Type;
use lang\XPClass;
use lang\ArrayType;
use lang\MapType;
use lang\FunctionType;
use unittest\actions\RuntimeVersion;

/**
 * Tests typeof() functionality
 */
class TypeOfTest extends \unittest\TestCase {

  #[@test]
  public function null() {
    $this->assertEquals(Type::$VOID, typeof(null));
  }

  #[@test]
  public function this() {
    $this->assertEquals(new XPClass(self::class), typeof($this));
  }

  #[@test]
  public function native() {
    $this->assertEquals(new XPClass(\ArrayObject::class), typeof(new \ArrayObject([])));
  }

  #[@test]
  public function string() {
    $this->assertEquals(Primitive::$STRING, typeof($this->name));
  }

  #[@test]
  public function intArray() {
    $this->assertEquals(ArrayType::forName('var[]'), typeof([1, 2, 3]));
  }

  #[@test]
  public function intMap() {
    $this->assertEquals(MapType::forName('[:var]'), typeof(['one' => 1, 'two' => 2, 'three' => 3]));
  }

  #[@test]
  public function function_without_arg() {
    $this->assertEquals(FunctionType::forName('function(): var'), typeof(function() { }));
  }

  #[@test]
  public function function_with_arg() {
    $this->assertEquals(FunctionType::forName('function(var): var'), typeof(function($a) { }));
  }

  #[@test]
  public function function_with_args() {
    $this->assertEquals(FunctionType::forName('function(var, var): var'), typeof(function($a, $b) { }));
  }

  #[@test]
  public function function_with_class_hint() {
    $this->assertEquals(FunctionType::forName('function(lang.Type): var'), typeof(function(Type $t) { }));
  }

  #[@test]
  public function function_with_array_hint() {
    $this->assertEquals(new FunctionType([Type::$ARRAY], Type::$VAR), typeof(function(array $a) { }));
  }

  #[@test]
  public function function_with_callable_hint() {
    $this->assertEquals(new FunctionType([Type::$CALLABLE], Type::$VAR), typeof(function(callable $c) { }));
  }

  #[@test, @action(new RuntimeVersion('>=7.0'))]
  public function function_with_primitive_arg() {
    $this->assertEquals(FunctionType::forName('function(int): var'), typeof(eval('return function(int $a) { };')));
  }

  #[@test, @action(new RuntimeVersion('>=7.0'))]
  public function function_with_return_type() {
    $this->assertEquals(FunctionType::forName('function(): lang.Type'), typeof(eval('return function(): \lang\Type { };')));
  }

  #[@test, @action(new RuntimeVersion('>=7.0'))]
  public function function_with_primitive_return_type() {
    $this->assertEquals(FunctionType::forName('function(): int'), typeof(eval('return function(): int { };')));
  }

  #[@test, @action(new RuntimeVersion('>=7.1'))]
  public function function_with_void_return_type() {
    $this->assertEquals(FunctionType::forName('function(): void'), typeof(eval('return function(): void { };')));
  }
}
