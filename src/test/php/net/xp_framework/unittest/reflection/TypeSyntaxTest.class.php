<?php namespace net\xp_framework\unittest\reflection;

use lang\{ClassLoader, Primitive, TypeUnion};
use unittest\actions\RuntimeVersion;
use unittest\{Action, Test, TestCase};

class TypeSyntaxTest extends TestCase {
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
    $this->assertEquals(Primitive::$STRING, $d->getType());
    $this->assertEquals('string', $d->getTypeName());
  }

  #[Test]
  public function return_primitive_type() {
    $d= $this->method('function fixture(): string { return "Test"; }');
    $this->assertEquals(Primitive::$STRING, $d->getReturnType());
    $this->assertEquals('string', $d->getReturnTypeName());
  }

  #[Test]
  public function parameter_primitive_type() {
    $d= $this->method('function fixture(string $name) { }');
    $this->assertEquals(Primitive::$STRING, $d->getParameter(0)->getType());
    $this->assertEquals('string', $d->getParameter(0)->getTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0")')]
  public function union_type() {
    $d= $this->field('private string|int $fixture;');
    $this->assertEquals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->getType());
    $this->assertEquals('string|int', $d->getTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0")')]
  public function return_union_type() {
    $d= $this->method('function fixture(): string|int { return "Test"; }');
    $this->assertEquals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->getReturnType());
    $this->assertEquals('string|int', $d->getReturnTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0")')]
  public function parameter_union_type() {
    $d= $this->method('function fixture(string|int $name) { }');
    $this->assertEquals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->getParameter(0)->getType());
    $this->assertEquals('string|int', $d->getParameter(0)->getTypeName());
  }
}