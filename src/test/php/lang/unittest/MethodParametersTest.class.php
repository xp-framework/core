<?php namespace lang\unittest;

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
use test\verify\Runtime;
use test\{Action, Assert, Expect, Test, Values};

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
    yield ['\lang\unittest\Name', new XPClass(Name::class)];
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
    Assert::equals($expected, $param->getType(), 'type');
    Assert::equals($expected->getName(), $param->getTypeName(), 'name');
  }

  #[Test]
  public function parameter_type_defaults_to_var() {
    $this->assertParamType(Type::$VAR, $this->method('public function fixture($param) { }')->getParameter(0));
  }

  #[Test, Values(from: 'types')]
  public function parameter_type_determined_via_apidoc($declaration, $type) {
    $this->assertParamType(
      $type,
      $this->method('/** @param '.$declaration.' */ public function fixture($param) { }')->getParameter(0)
    );
  }

  #[Test, Values(from: 'arrays')]
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

  #[Test, Values([['\lang\Value', Value::class], ['\lang\unittest\Name', Name::class], ['Value', Value::class]])]
  public function parameter_type_determined_via_syntax($literal, $type) {
    $this->assertParamType(
      new XPClass($type),
      $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)
    );
  }

  #[Test, Runtime(php: '>=7.0'), Values([['string'], ['int'], ['bool'], ['float']])]
  public function parameter_type_determined_via_scalar_syntax($literal) {
    $this->assertParamType(
      Primitive::forName($literal),
      $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)
    );
  }

  #[Test, Runtime(php: '>=7.1')]
  public function nullable_parameter_type() {
    $fixture= $this->type('{ public function fixture(?string $arg) { } }');
    $this->assertParamType(
      new Nullable(Primitive::$STRING),
      $fixture->getMethod('fixture')->getParameter(0)
    );
  }

  #[Test, Runtime(php: '>=8.0'), Values([['string|int'], ['string|false']])]
  public function parameter_type_determined_via_union_syntax($literal) {
    $this->assertParamType(
      TypeUnion::forName($literal),
      $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)
    );
  }

  #[Test]
  public function self_parameter_type() {
    $fixture= $this->type('{ public function fixture(self $param) { } }');
    Assert::equals($fixture, $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[Test]
  public function self_parameter_typeName() {
    $fixture= $this->type('{ public function fixture(self $param) { } }');
    Assert::equals('self', $fixture->getMethod('fixture')->getParameter(0)->getTypeName());
  }

  #[Test]
  public function self_parameter_type_via_apidoc() {
    $fixture= $this->type('{ /** @param self $param */ public function fixture($param) { } }');
    Assert::equals($fixture, $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[Test]
  public function self_parameter_typeName_via_apidoc() {
    $fixture= $this->type('{ /** @param self $param */ public function fixture($param) { } }');
    Assert::equals('self', $fixture->getMethod('fixture')->getParameter(0)->getTypeName());
  }

  #[Test]
  public function array_of_self_parameter_type_via_apidoc() {
    $fixture= $this->type('{ /** @param array<self> */ public function fixture($list) { } }');
    Assert::equals(new ArrayType($fixture), $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[Test]
  public function parent_parameter_type() {
    $fixture= $this->type('{ public function fixture(parent $param) { } }', [
      'extends' => [Name::class]
    ]);
    Assert::equals($fixture->getParentclass(), $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[Test]
  public function parent_parameter_typeName() {
    $fixture= $this->type('{ public function fixture(parent $param) { } }', [
      'extends' => [Name::class]
    ]);
    Assert::equals('parent', $fixture->getMethod('fixture')->getParameter(0)->getTypeName());
  }

  #[Test]
  public function parent_parameter_type_via_apidoc() {
    $fixture= $this->type('{ /** @param parent $param */ public function fixture($param) { } }', [
      'extends' => [Name::class]
    ]);
    Assert::equals($fixture->getParentclass(), $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[Test]
  public function parent_parameter_typeName_via_apidoc() {
    $fixture= $this->type('{ /** @param parent $param */ public function fixture($param) { } }', [
      'extends' => [Name::class]
    ]);
    Assert::equals('parent', $fixture->getMethod('fixture')->getParameter(0)->getTypeName());
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function nonexistant_type_class_parameter() {
    $this->method('public function fixture(UnknownTypeRestriction $param) { }')->getParameter(0)->getType();
  }

  #[Test]
  public function nonexistant_name_class_parameter() {
    Assert::equals(
      'lang.unittest.UnknownTypeRestriction',
      $this->method('public function fixture(UnknownTypeRestriction $param) { }')->getParameter(0)->getTypeName()
    );
  }

  #[Test]
  public function unrestricted_parameter() {
    Assert::null($this->method('public function fixture($param) { }')->getParameter(0)->getTypeRestriction());
  }

  #[Test]
  public function self_restricted_parameter() {
    $fixture= $this->type('{ public function fixture(self $param) { } }');
    Assert::equals(
      $fixture,
      $fixture->getMethod('fixture')->getParameter(0)->getTypeRestriction()
    );
  }

  #[Test]
  public function unrestricted_parameter_with_apidoc() {
    Assert::null(
      $this->method('/** @param lang.Value */ public function fixture($param) { }')->getParameter(0)->getTypeRestriction()
    );
  }

  #[Test, Values(from: 'restrictions')]
  public function type_restriction_determined_via_syntax($literal, $type) {
    Assert::equals(
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
    Assert::equals(0, $this->method('public function fixture() { }')->numParameters());
  }

  #[Test]
  public function three_parameters() {
    Assert::equals(3, $this->method('public function fixture($a, $b, $c) { }')->numParameters());
  }

  #[Test]
  public function no_parameters() {
    Assert::equals([], $this->method('public function fixture() { }')->getParameters());
  }

  #[Test]
  public function parameter_names() {
    Assert::equals(['a', 'b', 'c'], array_map(
      function($p) { return $p->getName(); },
      $this->method('public function fixture($a, $b, $c) { }')->getParameters()
    ));
  }

  #[Test, Values([-1, 0, 1])]
  public function accessing_a_parameter_via_non_existant_offset($offset) {
    Assert::null($this->method('public function fixture() { }')->getParameter($offset));
  }

  /** @return lang.reflect.Parameter */
  private function annotatedParameter() {
    try {
      $p= $this->method("#[@\$param: test('value')]\npublic function fixture(\$param) { }")->getParameter(0);
      $p->getAnnotations();
      return $p;
    } finally {
      \xp::gc(); // Strip deprecation warnings
    }
  }

  #[Test, Runtime(php: '<8.0')]
  public function annotated_parameter() {
    Assert::true($this->annotatedParameter()->hasAnnotations());
  }

  #[Test, Runtime(php: '<8.0')]
  public function parameter_annotated_with_test_has_test_annotation() {
    Assert::true($this->annotatedParameter()->hasAnnotation('test'));
  }

  #[Test, Runtime(php: '<8.0')]
  public function parameter_annotated_with_test_has_no_limit_annotation() {
    Assert::false($this->annotatedParameter()->hasAnnotation('limit'));
  }

  #[Test, Runtime(php: '<8.0')]
  public function annotations_of_parameter_annotated_with_test() {
    Assert::equals(['test' => 'value'], $this->annotatedParameter()->getAnnotations());
  }

  #[Test, Runtime(php: '<8.0')]
  public function test_annotation_of_parameter_annotated_with_test() {
    Assert::equals('value', $this->annotatedParameter()->getAnnotation('test'));
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
    Assert::equals(
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
    Assert::false($this->method('public function fixture($param) { }')->getParameter(0)->hasAnnotations());
  }

  #[Test]
  public function un_annotated_parameter_annotations_are_empty() {
    Assert::equals([], $this->method('public function fixture($param) { }')->getParameter(0)->getAnnotations());
  }

  #[Test, Expect(class: ElementNotFoundException::class, message: 'Annotation "test" does not exist')]
  public function cannot_get_test_annotation_for_un_annotated_parameter() {
    $this->method('public function fixture($param) { }')->getParameter(0)->getAnnotation('test');
  }

  #[Test]
  public function required_parameter() {
    Assert::false($this->method('public function fixture($param) { }')->getParameter(0)->isOptional());
  }

  #[Test]
  public function optional_parameter() {
    Assert::true($this->method('public function fixture($param= true) { }')->getParameter(0)->isOptional());
  }

  #[Test, Expect(class: IllegalStateException::class, message: 'Parameter "param" has no default value')]
  public function required_parameter_does_not_have_default_value() {
    $this->method('public function fixture($param) { }')->getParameter(0)->getDefaultValue();
  }

  #[Test]
  public function optional_parameters_default_value() {
    Assert::equals(true, $this->method('public function fixture($param= true) { }')->getParameter(0)->getDefaultValue());
  }

  #[Test]
  public function default_annotation_may_supply_default_value() {
    $method= $this->method('public function fixture($param= null) { }');

    // Directly modify meta information for this test's purpose
    // See https://github.com/xp-framework/compiler/pull/104#issuecomment-791924395
    \xp::$meta[$method->getDeclaringClass()->getName()][1][$method->getName()]= [
      DETAIL_TARGET_ANNO => ['$param' => ['default' => $this]]
    ];

    Assert::equals($this, $method->getParameter(0)->getDefaultValue());
  }

  #[Test]
  public function vararg_parameters_default_value() {
    Assert::equals(null, $this->method('public function fixture(... $param) { }')->getParameter(0)->getDefaultValue());
  }

  #[Test, Values([['/** @param string */ function fixture($a)', 'lang.reflect.Parameter<lang.Primitive<string> a>'], ['/** @param lang.Value */ function fixture($a)', 'lang.reflect.Parameter<lang.XPClass<lang.Value> a>'], ['/** @param \lang\Value */ function fixture($a)', 'lang.reflect.Parameter<lang.XPClass<lang.Value> a>'], ['function fixture(\lang\Value $a)', 'lang.reflect.Parameter<lang.XPClass<lang.Value> a>'], ['/** @param var[] */ function fixture($a)', 'lang.reflect.Parameter<lang.ArrayType<var[]> a>'], ['/** @param function(string): int */ function fixture($a)', 'lang.reflect.Parameter<lang.FunctionType<(function(string): int)> a>'], ['/** @param bool */ function fixture($a= true)', 'lang.reflect.Parameter<lang.Primitive<bool> a= true>']])]
  public function parameter_representations($declaration, $expected) {
    Assert::equals($expected, $this->method($declaration.' { }')->getParameter(0)->toString());
  }

  #[Test]
  public function variadic_via_syntax_with_type() {
    $param= $this->method('function fixture(string... $args) { }')->getParameter(0);
    Assert::equals(
      ['variadic' => true, 'optional' => true, 'type' => Primitive::$STRING],
      ['variadic' => $param->isVariadic(), 'optional' => $param->isOptional(), 'type' => $param->getType()]
    );
  }

  #[Test]
  public function variadic_via_syntax() {
    $param= $this->method('function fixture(... $args) { }')->getParameter(0);
    Assert::equals(
      ['variadic' => true, 'optional' => true, 'type' => Type::$VAR],
      ['variadic' => $param->isVariadic(), 'optional' => $param->isOptional(), 'type' => $param->getType()]
    );
  }

  #[Test]
  public function variadic_via_apidoc() {
    $param= $this->method('/** @param var... $args */ function fixture($args= null) { }')->getParameter(0);
    Assert::equals(
      ['variadic' => true, 'optional' => true, 'type' => Type::$VAR],
      ['variadic' => $param->isVariadic(), 'optional' => $param->isOptional(), 'type' => $param->getType()]
    );
  }
}