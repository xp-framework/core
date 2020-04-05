<?php namespace net\xp_framework\unittest\reflection;

use lang\{Primitive, TypeUnion, ClassLoader};
use unittest\TestCase;
use unittest\actions\RuntimeVersion;

class TypeSyntaxTest extends TestCase {

  /**
   * Declare a method from given source code
   *
   * @param  string $source
   * @return lang.reflect.Method
   */
  private function declaration($source) {
    static $id= 0;
    static $spec= ['kind' => 'class', 'extends' => null, 'implements' => [], 'use' => []];

    return ClassLoader::defineType(self::class.(++$id), $spec, '{'.$source.'}')->getMethod('fixture');
  }

  #[@test]
  public function return_primitive_type() {
    $d= $this->declaration('function fixture(): string { return "Test"; }');
    $this->assertEquals(Primitive::$STRING, $d->getReturnType());
  }

  #[@test]
  public function parameter_primitive_type() {
    $d= $this->declaration('function fixture(string $name) { }');
    $this->assertEquals(Primitive::$STRING, $d->getParameter(0)->getType());
  }

  #[@test, @action(new RuntimeVersion('>=8.0'))]
  public function return_union_type() {
    $d= $this->declaration('function fixture(): string|int { return "Test"; }');
    $this->assertEquals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->getReturnType());
  }

  #[@test, @action(new RuntimeVersion('>=8.0'))]
  public function parameter_union_type() {
    $d= $this->declaration('function fixture(string|int $name) { }');
    $this->assertEquals(new TypeUnion([Primitive::$STRING, Primitive::$INT]), $d->getParameter(0)->getType());
  }
}