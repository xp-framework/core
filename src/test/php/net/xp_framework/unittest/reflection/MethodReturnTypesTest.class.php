<?php namespace net\xp_framework\unittest\reflection;

use lang\{ArrayType, MapType, FunctionType, Primitive, Type, TypeUnion, Value, XPClass};
use net\xp_framework\unittest\Name;
use unittest\actions\RuntimeVersion;
use unittest\{Action, Test, Values};

class MethodReturnTypesTest extends MethodsTest {

  /**
   * Assertion helper
   *
   * @param  lang.Type $expected
   * @param  lang.reflect.Method $method
   */
  private function assertReturnType($expected, $method) {
    $this->assertEquals($expected, $method->getReturnType(), 'type');
    $this->assertEquals($expected->getName(), $method->getReturnTypeName(), 'name');
  }

  /** @return iterable */
  private function types() {
    yield ['void', Type::$VOID];
    yield ['var', Type::$VAR];
    yield ['bool', Primitive::$BOOL];
    yield ['string[]', new ArrayType(Primitive::$STRING)];
    yield ['[:int]', new MapType(Primitive::$INT)];
    yield ['lang.Value', new XPClass(Value::class)];
    yield ['Value', new XPClass(Value::class)];
    yield ['\\lang\\Value', new XPClass(Value::class)];
  }

  /** @return iterable */
  private function arrays() {
    yield ['string[]', new ArrayType(Primitive::$STRING)];
    yield ['[:int]', new MapType(Primitive::$INT)];
  }

  /** @return iterable */
  private function restrictions() {
    yield ['string', Primitive::$STRING];
    yield ['array', Type::$ARRAY];
    yield ['callable', Type::$CALLABLE];
    yield ['\lang\Value', new XPClass(Value::class)];
    yield ['Value', new XPClass(Value::class)];
  }

  #[Test]
  public function return_type_defaults_to_var() {
    $this->assertReturnType(Type::$VAR, $this->method('public function fixture() { }'));
  }

  #[Test]
  public function return_type_restriction_defaults_to_null() {
    $this->assertNull($this->method('public function fixture() { }')->getReturnTypeRestriction());
  }

  #[Test]
  public function return_type_restriction() {
    $this->assertEquals(
      new XPClass(Value::class),
      $this->method('public function fixture(): Value { }')->getReturnTypeRestriction()
    );
  }

  #[Test]
  public function return_type_inherited() {
    $this->assertReturnType(
      Primitive::$STRING,
      $this->type('{ }', ['extends' => [Name::class]])->getMethod('hashCode')
    );
  }

  #[Test, Values('types')]
  public function return_type_determined_via_apidoc($declaration, $type) {
    $this->assertReturnType(
      $type,
      $this->method('/** @return '.$declaration.' */ public function fixture() { }')
    );
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=7.0")'), Values('restrictions')]
  public function return_type_determined_via_syntax($literal, $type) {
    $this->assertReturnType($type, $this->method('public function fixture(): '.$literal.' { }'));
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=7.1")')]
  public function void_return_type() {
    $fixture= $this->type('{ public function fixture(): void { } }');
    $this->assertEquals(Type::$VOID, $fixture->getMethod('fixture')->getReturnType());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0")'), Values([['string|int'], ['string|false']])]
  public function return_type_determined_via_union_syntax($literal) {
    $this->assertReturnType(
      TypeUnion::forName($literal),
      $this->method('public function fixture(): '.$literal.' { }')
    );
  }

  #[Test, Values('arrays')]
  public function specific_array_type_determined_via_apidoc_if_present($declaration, $type) {
    $this->assertReturnType(
      $type,
      $this->method('/** @return '.$declaration.' */ public function fixture(): array { }')
    );
  }

  #[Test]
  public function specific_callable_type_determined_via_apidoc_if_present() {
    $this->assertReturnType(
      new FunctionType([], Primitive::$STRING),
      $this->method('/** @return (function(): string) */ public function fixture(): callable { }')
    );
  }

  #[Test]
  public function special_self_return_type_via_apidoc() {
    $fixture= $this->type('{ /** @return self */ public function fixture() { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getReturnType());
  }

  #[Test]
  public function special_self_return_type_via_syntax() {
    $fixture= $this->type('{ public function fixture(): self { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getReturnType());
  }

  #[Test]
  public function special_parent_return_type_via_apidoc() {
    $fixture= $this->type('{ /** @return parent */ public function fixture() { } }', [
      'extends' => [Name::class]
    ]);
    $this->assertEquals($fixture->getParentclass(), $fixture->getMethod('fixture')->getReturnType());
  }

  #[Test]
  public function special_parent_return_type_via_syntax() {
    $fixture= $this->type('{ public function fixture(): parent { } }', [
      'extends' => [Name::class]
    ]);
    $this->assertEquals($fixture->getParentclass(), $fixture->getMethod('fixture')->getReturnType());
  }

  #[Test]
  public function special_static_return_type_in_base_class() {
    $fixture= new XPClass(Name::class);
    $this->assertEquals($fixture, $fixture->getMethod('copy')->getReturnType());
  }

  #[Test]
  public function special_static_return_type_in_inherited_class() {
    $fixture= $this->type('{ }', ['extends' => [Name::class]]);
    $this->assertEquals($fixture, $fixture->getMethod('copy')->getReturnType());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0")')]
  public function special_static_return_type_via_syntax() {
    $fixture= $this->type('{ public function fixture(): static { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getReturnType());
  }

  #[Test, Values([['/** @return static */', 'static'], ['/** @return self */', 'self'], ['/** @return parent */', 'parent'],])]
  public function special_typeName_determined_via_apidoc($apidoc, $type) {
    $this->assertEquals($type, $this->method($apidoc.' public function fixture() { }')->getReturnTypeName());
  }

  #[Test]
  public function apidoc_supersedes_self_type_restriction() {
    $base= $this->type('{ /** @return static */ public function fixture(): self { } }');
    $fixture= $this->type('{ /* inherited with apidoc */ }', ['extends' => [$base]]);
    $method= $fixture->getMethod('fixture');

    $this->assertEquals($fixture, $method->getReturnType(), 'type');
    $this->assertEquals('static', $method->getReturnTypeName(), 'name');
  }

  #[Test]
  public function self_type_restriction_inheritance() {
    $base= $this->type('{ public function fixture(): self { } }');
    $fixture= $this->type('{ /* inherited without apidoc */ }', ['extends' => [$base]]);
    $method= $fixture->getMethod('fixture');

    $this->assertEquals($base, $method->getReturnType(), 'type');
    $this->assertEquals('self', $method->getReturnTypeName(), 'name');
  }

  #[Test]
  public function array_of_special_self_type() {
    $fixture= $this->type('{ /** @return array<self> */ public function fixture() { } }');
    $this->assertEquals(new ArrayType($fixture), $fixture->getMethod('fixture')->getReturnType());
  }
}