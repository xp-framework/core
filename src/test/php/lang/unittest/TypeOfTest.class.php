<?php namespace lang\unittest;

use lang\{ArrayType, FunctionType, MapType, Primitive, Type, XPClass};
use unittest\actions\RuntimeVersion;
use unittest\{Assert, Action, Test};

class TypeOfTest {

  #[Test]
  public function null() {
    Assert::equals(Type::$VOID, typeof(null));
  }

  #[Test]
  public function this() {
    Assert::equals(new XPClass(self::class), typeof($this));
  }

  #[Test]
  public function native() {
    Assert::equals(new XPClass(\ArrayObject::class), typeof(new \ArrayObject([])));
  }

  #[Test]
  public function string() {
    Assert::equals(Primitive::$STRING, typeof('Test'));
  }

  #[Test]
  public function intArray() {
    Assert::equals(ArrayType::forName('var[]'), typeof([1, 2, 3]));
  }

  #[Test]
  public function intMap() {
    Assert::equals(MapType::forName('[:var]'), typeof(['one' => 1, 'two' => 2, 'three' => 3]));
  }

  #[Test]
  public function function_without_arg() {
    Assert::equals(FunctionType::forName('function(): var'), typeof(function() { }));
  }

  #[Test]
  public function function_with_arg() {
    Assert::equals(FunctionType::forName('function(var): var'), typeof(function($a) { }));
  }

  #[Test]
  public function function_with_args() {
    Assert::equals(FunctionType::forName('function(var, var): var'), typeof(function($a, $b) { }));
  }

  #[Test]
  public function function_with_var_arg() {
    Assert::equals(FunctionType::forName('function(): var'), typeof(function(... $a) { }));
  }

  #[Test]
  public function function_with_normal_and_var_arg() {
    Assert::equals(FunctionType::forName('function(lang.Type): var'), typeof(function(Type $t, ... $a) { }));
  }

  #[Test]
  public function function_with_typed_var_arg() {
    Assert::equals(FunctionType::forName('function(): var'), typeof(eval('return function(\lang\Type... $a) { };')));
  }

  #[Test]
  public function function_with_class_hint() {
    Assert::equals(FunctionType::forName('function(lang.Type): var'), typeof(function(Type $t) { }));
  }

  #[Test]
  public function function_with_array_hint() {
    Assert::equals(new FunctionType([Type::$ARRAY], Type::$VAR), typeof(function(array $a) { }));
  }

  #[Test]
  public function function_with_callable_hint() {
    Assert::equals(new FunctionType([Type::$CALLABLE], Type::$VAR), typeof(function(callable $c) { }));
  }

  #[Test]
  public function function_with_primitive_arg() {
    Assert::equals(FunctionType::forName('function(int): var'), typeof(eval('return function(int $a) { };')));
  }

  #[Test]
  public function function_with_return_type() {
    Assert::equals(FunctionType::forName('function(): lang.Type'), typeof(eval('return function(): \lang\Type { };')));
  }

  #[Test]
  public function function_with_primitive_return_type() {
    Assert::equals(FunctionType::forName('function(): int'), typeof(eval('return function(): int { };')));
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=7.1")')]
  public function function_with_nullable_return_type() {
    Assert::equals(FunctionType::forName('function(): ?string'), typeof(eval('return function(): ?string { };')));
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=7.1")')]
  public function function_with_void_return_type() {
    Assert::equals(FunctionType::forName('function(): void'), typeof(eval('return function(): void { };')));
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0.0-dev")')]
  public function php8_native_union_param_type() {
    Assert::equals(FunctionType::forName('function(string|int): var'), typeof(eval('return function(string|int $a) { };')));
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0.0-dev")')]
  public function php8_native_union_return_type() {
    Assert::equals(FunctionType::forName('function(): string|int'), typeof(eval('return function(): string|int { };')));
  }
}