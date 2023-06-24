<?php namespace net\xp_framework\unittest\reflection;

use lang\{ClassLoader, Nullable, Primitive, TypeUnion};
use unittest\actions\RuntimeVersion;
use unittest\{Assert, Action, Test};

class TypeSyntaxTest {
  private static $spec= ['kind' => 'class', 'extends' => null, 'implements' => [], 'use' => []];

  /**
   * Declare a field from given source code
   *
   * @param  string $source
   * @return lang.reflect.Field
   */
  private function field($source) {
    static $id= 0;
    return ClassLoader::defineType(self::class.'Field'.(++$id), self::$spec, '{'.$source.'}')->getField('fixture');
  }

  /**
   * Declare a method from given source code
   *
   * @param  string $source
   * @return lang.reflect.Method
   */
  private function method($source) {
    static $id= 0;
    return ClassLoader::defineType(self::class.'Method'.(++$id), self::$spec, '{'.$source.'}')->getMethod('fixture');
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=7.4")')]
  public function primitive_type() {
    $d= $this->field('private string $fixture;');
    Assert::equals(Primitive::$STRING, $d->getType());
    Assert::equals('string', $d->getTypeName());
  }

  #[Test]
  public function return_primitive_type() {
    $d= $this->method('function fixture(): string { return "Test"; }');
    Assert::equals(Primitive::$STRING, $d->getReturnType());
    Assert::equals('string', $d->getReturnTypeName());
  }

  #[Test]
  public function parameter_primitive_type() {
    $d= $this->method('function fixture(string $name) { }');
    Assert::equals(Primitive::$STRING, $d->getParameter(0)->getType());
    Assert::equals('string', $d->getParameter(0)->getTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0")')]
  public function union_type() {
    $d= $this->field('private string|int $fixture;');
    Assert::equals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->getType());
    Assert::equals('string|int', $d->getTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0")')]
  public function nullable_union_type() {
    $d= $this->field('private string|int|null $fixture;');
    Assert::equals(new Nullable(new TypeUnion([Primitive::$STRING, Primitive::$INT])), $d->getType());
    Assert::equals('?string|int', $d->getTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0")')]
  public function return_union_type() {
    $d= $this->method('function fixture(): string|int { return "Test"; }');
    Assert::equals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->getReturnType());
    Assert::equals('string|int', $d->getReturnTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0")')]
  public function parameter_union_type() {
    $d= $this->method('function fixture(string|int $name) { }');
    Assert::equals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->getParameter(0)->getType());
    Assert::equals('string|int', $d->getParameter(0)->getTypeName());
  }
}