<?php namespace net\xp_framework\unittest\reflection;

use lang\{
  ArrayType,
  ClassNotFoundException,
  ElementNotFoundException,
  FunctionType,
  IllegalStateException,
  MapType,
  Primitive,
  TypeUnion,
  Type,
  Value,
  Nullable,
  XPClass
};
use net\xp_framework\unittest\Name;
use unittest\actions\RuntimeVersion;
use unittest\{Action, Expect, Test, Values};

class MethodParametersTest extends MethodsTest {

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
    yield ['\lang\Value', new XPClass(Value::class)];
    yield ['\net\xp_framework\unittest\Name', new XPClass(Name::class)];
    yield ['array', Type::$ARRAY];
    yield ['callable', Type::$CALLABLE];
  }

  /**
   * Assertion helper
   *
   * @param  lang.Type $expected
   * @param  lang.reflect.Parameter $param
   */
  private function assertParamType($expected, $param) {
    $this->assertEquals($expected, $param->getType(), 'type');
    $this->assertEquals($expected->getName(), $param->getTypeName(), 'name');
  }

  #[Test]
  public function parameter_type_defaults_to_var() {
    $this->assertParamType(Type::$VAR, $this->method('public function fixture($param) { }')->getParameter(0));
  }

  #[Test, Values('types')]
  public function parameter_type_determined_via_apidoc($declaration, $type) {
    $this->assertParamType(
      $type,
      $this->method('/** @param '.$declaration.' */ public function fixture($param) { }')->getParameter(0)
    );
  }

  #[Test, Values('arrays')]
  public function specific_array_type_determined_via_apidoc_if_present($declaration, $type) {
    $this->assertParamType(
      $type,
      $this->method('/** @param '.$declaration.' */ public function fixture(array $param) { }')->getParameter(0)
    );
  }

  #[Test]
  public function specific_callable_type_determined_via_apidoc_if_present() {
    $this->assertParamType(
      new FunctionType([], Primitive::$STRING),
      $this->method('/** @param (function(): string) */ public function fixture(callable $param) { }')->getParameter(0)
    );
  }

  #[Test, Values([['\lang\Value', Value::class], ['\net\xp_framework\unittest\Name', Name::class], ['Value', Value::class]])]
  public function parameter_type_determined_via_syntax($literal, $type) {
    $this->assertParamType(
      new XPClass($type),
      $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)
    );
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=7.0")'), Values([['string'], ['int'], ['bool'], ['float']])]
  public function parameter_type_determined_via_scalar_syntax($literal) {
    $this->assertParamType(
      Primitive::forName($literal),
      $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)
    );
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=7.1")')]
  public function nullable_parameter_type() {
    $fixture= $this->type('{ public function fixture(?string $arg) { } }');
    $this->assertParamType(
      new Nullable(Primitive::$STRING),
      $fixture->getMethod('fixture')->getParameter(0)
    );
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.0")'), Values([['string|int'], ['string|false']])]
  public function parameter_type_determined_via_union_syntax($literal) {
    $this->assertParamType(
      TypeUnion::forName($literal),
      $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)
    );
  }

  #[Test]
  public function self_parameter_type() {
    $fixture= $this->type('{ public function fixture(self $param) { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[Test]
  public function self_parameter_typeName() {
    $fixture= $this->type('{ public function fixture(self $param) { } }');
    $this->assertEquals('self', $fixture->getMethod('fixture')->getParameter(0)->getTypeName());
  }

  #[Test]
  public function self_parameter_type_via_apidoc() {
    $fixture= $this->type('{ /** @param self $param */ public function fixture($param) { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[Test]
  public function self_parameter_typeName_via_apidoc() {
    $fixture= $this->type('{ /** @param self $param */ public function fixture($param) { } }');
    $this->assertEquals('self', $fixture->getMethod('fixture')->getParameter(0)->getTypeName());
  }

  #[Test]
  public function array_of_self_parameter_type_via_apidoc() {
    $fixture= $this->type('{ /** @param array<self> */ public function fixture($list) { } }');
    $this->assertEquals(new ArrayType($fixture), $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[Test]
  public function parent_parameter_type() {
    $fixture= $this->type('{ public function fixture(parent $param) { } }', [
      'extends' => [Name::class]
    ]);
    $this->assertEquals($fixture->getParentclass(), $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[Test]
  public function parent_parameter_typeName() {
    $fixture= $this->type('{ public function fixture(parent $param) { } }', [
      'extends' => [Name::class]
    ]);
    $this->assertEquals('parent', $fixture->getMethod('fixture')->getParameter(0)->getTypeName());
  }

  #[Test]
  public function parent_parameter_type_via_apidoc() {
    $fixture= $this->type('{ /** @param parent $param */ public function fixture($param) { } }', [
      'extends' => [Name::class]
    ]);
    $this->assertEquals($fixture->getParentclass(), $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[Test]
  public function parent_parameter_typeName_via_apidoc() {
    $fixture= $this->type('{ /** @param parent $param */ public function fixture($param) { } }', [
      'extends' => [Name::class]
    ]);
    $this->assertEquals('parent', $fixture->getMethod('fixture')->getParameter(0)->getTypeName());
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function nonexistant_type_class_parameter() {
    $this->method('public function fixture(UnknownTypeRestriction $param) { }')->getParameter(0)->getType();
  }

  #[Test]
  public function nonexistant_name_class_parameter() {
    $this->assertEquals(
      'net.xp_framework.unittest.reflection.UnknownTypeRestriction',
      $this->method('public function fixture(UnknownTypeRestriction $param) { }')->getParameter(0)->getTypeName()
    );
  }

  #[Test]
  public function unrestricted_parameter() {
    $this->assertNull($this->method('public function fixture($param) { }')->getParameter(0)->getTypeRestriction());
  }

  #[Test]
  public function self_restricted_parameter() {
    $fixture= $this->type('{ public function fixture(self $param) { } }');
    $this->assertEquals(
      $fixture,
      $fixture->getMethod('fixture')->getParameter(0)->getTypeRestriction()
    );
  }

  #[Test]
  public function unrestricted_parameter_with_apidoc() {
    $this->assertNull(
      $this->method('/** @param lang.Value */ public function fixture($param) { }')->getParameter(0)->getTypeRestriction()
    );
  }

  #[Test, Values('restrictions')]
  public function type_restriction_determined_via_syntax($literal, $type) {
    $this->assertEquals(
      $type,
      $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)->getTypeRestriction()
    );
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function nonexistant_restriction_class_parameter() {
    $this->method('public function fixture(UnknownTypeRestriction $param) { }')->getParameter(0)->getTypeRestriction();
  }

  #[Test]
  public function zero_parameters() {
    $this->assertEquals(0, $this->method('public function fixture() { }')->numParameters());
  }

  #[Test]
  public function three_parameters() {
    $this->assertEquals(3, $this->method('public function fixture($a, $b, $c) { }')->numParameters());
  }

  #[Test]
  public function no_parameters() {
    $this->assertEquals([], $this->method('public function fixture() { }')->getParameters());
  }

  #[Test]
  public function parameter_names() {
    $this->assertEquals(['a', 'b', 'c'], array_map(
      function($p) { return $p->getName(); },
      $this->method('public function fixture($a, $b, $c) { }')->getParameters()
    ));
  }

  #[Test, Values([-1, 0, 1])]
  public function accessing_a_parameter_via_non_existant_offset($offset) {
    $this->assertNull($this->method('public function fixture() { }')->getParameter($offset));
  }

  #[Test, Action(eval: 'new RuntimeVersion("<8.0")')]
  public function annotated_parameter() {
    $this->assertTrue($this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0)->hasAnnotations());
  }

  #[Test, Action(eval: 'new RuntimeVersion("<8.0")')]
  public function parameter_annotated_with_test_has_test_annotation() {
    $this->assertTrue($this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0)->hasAnnotation('test'));
  }

  #[Test, Action(eval: 'new RuntimeVersion("<8.0")')]
  public function parameter_annotated_with_test_has_no_limit_annotation() {
    $this->assertFalse($this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0)->hasAnnotation('limit'));
  }

  #[Test, Action(eval: 'new RuntimeVersion("<8.0")')]
  public function annotations_of_parameter_annotated_with_test() {
    $this->assertEquals(['test' => 'value'], $this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0)->getAnnotations());
  }

  #[Test, Action(eval: 'new RuntimeVersion("<8.0")')]
  public function test_annotation_of_parameter_annotated_with_test() {
    $this->assertEquals('value', $this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0)->getAnnotation('test'));
  }

  #[Test]
  public function parameters_with_attribute() {
    $method= $this->method('
      #[Get]
      public function fixture(
        $user,
        #[Param]
        $sort,
        #[Param("max")]
        $limit,
        #[Param, Optional]
        $order= "asc"
      ) { }'
    );
    $r= [];
    foreach ($method->getParameters() as $param) {
      $r[$param->getName()]= $param->getAnnotations();
    }
    $this->assertEquals(
      [
        'user'  => [],
        'sort'  => ['param' => null],
        'limit' => ['param' => 'max'],
        'order' => ['param' => null, 'optional' => null]
      ],
      $r
    );
  }

  #[Test]
  public function un_annotated_parameter_has_no_annotations() {
    $this->assertFalse($this->method('public function fixture($param) { }')->getParameter(0)->hasAnnotations());
  }

  #[Test]
  public function un_annotated_parameter_annotations_are_empty() {
    $this->assertEquals([], $this->method('public function fixture($param) { }')->getParameter(0)->getAnnotations());
  }

  #[Test, Expect(['class' => ElementNotFoundException::class, 'withMessage' => 'Annotation "test" does not exist'])]
  public function cannot_get_test_annotation_for_un_annotated_parameter() {
    $this->method('public function fixture($param) { }')->getParameter(0)->getAnnotation('test');
  }

  #[Test]
  public function required_parameter() {
    $this->assertFalse($this->method('public function fixture($param) { }')->getParameter(0)->isOptional());
  }

  #[Test]
  public function optional_parameter() {
    $this->assertTrue($this->method('public function fixture($param= true) { }')->getParameter(0)->isOptional());
  }

  #[Test, Expect(['class' => IllegalStateException::class, 'withMessage' => 'Parameter "param" has no default value'])]
  public function required_parameter_does_not_have_default_value() {
    $this->method('public function fixture($param) { }')->getParameter(0)->getDefaultValue();
  }

  #[Test]
  public function optional_parameters_default_value() {
    $this->assertEquals(true, $this->method('public function fixture($param= true) { }')->getParameter(0)->getDefaultValue());
  }

  #[Test]
  public function vararg_parameters_default_value() {
    $this->assertEquals(null, $this->method('public function fixture(... $param) { }')->getParameter(0)->getDefaultValue());
  }

  #[Test, Values([['/** @param string */ function fixture($a)', 'lang.reflect.Parameter<lang.Primitive<string> a>'], ['/** @param lang.Value */ function fixture($a)', 'lang.reflect.Parameter<lang.XPClass<lang.Value> a>'], ['/** @param \lang\Value */ function fixture($a)', 'lang.reflect.Parameter<lang.XPClass<lang.Value> a>'], ['function fixture(\lang\Value $a)', 'lang.reflect.Parameter<lang.XPClass<lang.Value> a>'], ['/** @param var[] */ function fixture($a)', 'lang.reflect.Parameter<lang.ArrayType<var[]> a>'], ['/** @param function(string): int */ function fixture($a)', 'lang.reflect.Parameter<lang.FunctionType<(function(string): int)> a>'], ['/** @param bool */ function fixture($a= true)', 'lang.reflect.Parameter<lang.Primitive<bool> a= true>']])]
  public function parameter_representations($declaration, $expected) {
    $this->assertEquals($expected, $this->method($declaration.' { }')->getParameter(0)->toString());
  }

  #[Test]
  public function variadic_via_syntax_with_type() {
    $param= $this->method('function fixture(string... $args) { }')->getParameter(0);
    $this->assertEquals(
      ['variadic' => true, 'optional' => true, 'type' => Primitive::$STRING],
      ['variadic' => $param->isVariadic(), 'optional' => $param->isOptional(), 'type' => $param->getType()]
    );
  }

  #[Test]
  public function variadic_via_syntax() {
    $param= $this->method('function fixture(... $args) { }')->getParameter(0);
    $this->assertEquals(
      ['variadic' => true, 'optional' => true, 'type' => Type::$VAR],
      ['variadic' => $param->isVariadic(), 'optional' => $param->isOptional(), 'type' => $param->getType()]
    );
  }

  #[Test]
  public function variadic_via_apidoc() {
    $param= $this->method('/** @param var... $args */ function fixture($args= null) { }')->getParameter(0);
    $this->assertEquals(
      ['variadic' => true, 'optional' => true, 'type' => Type::$VAR],
      ['variadic' => $param->isVariadic(), 'optional' => $param->isOptional(), 'type' => $param->getType()]
    );
  }
}