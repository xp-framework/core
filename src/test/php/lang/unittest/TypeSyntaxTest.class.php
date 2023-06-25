<?php namespace lang\unittest;

use lang\{ClassLoader, Nullable, Primitive, TypeUnion, Reflection};
use test\verify\Runtime;
use test\{Action, Assert, Test};

class TypeSyntaxTest {
  private static $spec= ['kind' => 'class', 'extends' => null, 'implements' => [], 'use' => []];

  /**
   * Declare a propertyy from given source code
   *
   * @param  string $source
   * @return lang.reflection.Property
   */
  private function propertyy($source) {
    static $id= 0;

    $t= ClassLoader::defineType(self::class.'Propertyy'.(++$id), self::$spec, '{'.$source.'}');
    return Reflection::type($t)->property('fixture');
  }

  /**
   * Declare a method from given source code
   *
   * @param  string $source
   * @return lang.reflection.Method
   */
  private function method($source) {
    static $id= 0;
    $t= ClassLoader::defineType(self::class.'Method'.(++$id), self::$spec, '{'.$source.'}');
    return Reflection::type($t)->method('fixture');
  }

  #[Test, Runtime(php: '>=7.4')]
  public function primitive_type() {
    $d= $this->propertyy('private string $fixture;');
    Assert::equals(Primitive::$STRING, $d->constraint()->type());
  }

  #[Test]
  public function return_primitive_type() {
    $d= $this->method('function fixture(): string { return "Test"; }');
    Assert::equals(Primitive::$STRING, $d->returns()->type());
  }

  #[Test]
  public function parameter_primitive_type() {
    $d= $this->method('function fixture(string $name) { }');
    Assert::equals(Primitive::$STRING, $d->parameter(0)->constraint()->type());
  }

  #[Test, Runtime(php: '>=8.0')]
  public function union_type() {
    $d= $this->propertyy('private string|int $fixture;');
    Assert::equals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->constraint()->type());
  }

  #[Test, Runtime(php: '>=8.0')]
  public function nullable_union_type() {
    $d= $this->propertyy('private string|int|null $fixture;');
    Assert::equals(new Nullable(new TypeUnion([Primitive::$STRING, Primitive::$INT])), $d->constraint()->type());
  }

  #[Test, Runtime(php: '>=8.0')]
  public function return_union_type() {
    $d= $this->method('function fixture(): string|int { return "Test"; }');
    Assert::equals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->returns()->type());
  }

  #[Test, Runtime(php: '>=8.0')]
  public function parameter_union_type() {
    $d= $this->method('function fixture(string|int $name) { }');
    Assert::equals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->parameter(0)->constraint()->type());
  }
}