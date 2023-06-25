<?php namespace lang\unittest;

use lang\reflect\TargetInvocationException;
use lang\{ArrayType, ClassCastException, FunctionType, IllegalArgumentException, MapType, Primitive, Type, XPClass};
use test\verify\Condition;
use test\{Assert, Expect, Test, Values};

class FunctionTypeTest extends BaseTest {

  /** Fixture */
  public function getName(bool $compound= false): string { return 'test'; }

  #[Test]
  public function can_create_with_type_instances() {
    new FunctionType([Primitive::$STRING], Primitive::$STRING);
  }

  #[Test]
  public function can_create_with_type_names() {
    new FunctionType(['string'], 'string');
  }

  #[Test]
  public function returns() {
    Assert::equals(Type::$VOID, (new FunctionType([Primitive::$STRING], Type::$VOID))->returns());
  }

  #[Test]
  public function signature() {
    Assert::equals([Primitive::$STRING], (new FunctionType([Primitive::$STRING], Type::$VOID))->signature());
  }

  #[Test]
  public function a_function_accepting_one_string_arg_and_returning_a_string() {
    Assert::equals(
      new FunctionType([Primitive::$STRING], Primitive::$STRING),
      FunctionType::forName('function(string): string')
    );
  }

  #[Test]
  public function a_function_accepting_two_string_args_and_returning_a_string() {
    Assert::equals(
      new FunctionType([Primitive::$STRING, Primitive::$STRING], Primitive::$STRING),
      FunctionType::forName('function(string, string): string')
    );
  }

  #[Test]
  public function a_zero_arg_function_which_returns_bool() {
    Assert::equals(
      new FunctionType([], Primitive::$BOOL),
      FunctionType::forName('function(): bool')
    );
  }

  #[Test]
  public function a_zero_arg_function_which_returns_a_function_type() {
    Assert::equals(
      new FunctionType([], new FunctionType([Primitive::$STRING], Primitive::$INT)),
      FunctionType::forName('function(): function(string): int')
    );
  }

  #[Test]
  public function a_function_which_accepts_a_function_type() {
    Assert::equals(
      new FunctionType([new FunctionType([Primitive::$STRING], Primitive::$INT)], Type::$VAR),
      FunctionType::forName('function(function(string): int): var')
    );
  }

  #[Test]
  public function a_function_accepting_an_array_of_generic_objects_and_not_returning_anything() {
    Assert::equals(
      new FunctionType([new ArrayType(XPClass::forName('lang.Value'))], Type::$VOID),
      FunctionType::forName('function(lang.Value[]): void')
    );
  }

  #[Test]
  public function function_with_zero_args_is_instance_of_zero_arg_function_type() {
    Assert::true((new FunctionType([], Type::$VAR))->isInstance(
      function() { }
    ));
  }

  #[Test]
  public function function_with_two_args_is_instance_of_two_arg_function_type() {
    Assert::true((new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->isInstance(
      function($a, $b) { }
    ));
  }

  #[Test]
  public function function_with_type_hinted_arg_is_instance_of_function_type_with_class_signature() {
    Assert::true((new FunctionType([XPClass::forName('lang.XPClass')], Type::$VAR))->isInstance(
      function(XPClass $c) { }
    ));
  }

  #[Test]
  public function function_with_array_hinted_arg_is_instance_of_function_type_with_array_signature() {
    Assert::true((new FunctionType([Type::$ARRAY], Type::$VAR))->isInstance(
      function(array $a) { }
    ));
  }

  #[Test]
  public function function_with_callable_hinted_arg_is_instance_of_function_type_with_function_signature() {
    Assert::true((new FunctionType([new FunctionType(null, Type::$VAR)], Type::$VAR))->isInstance(
      function(callable $a) { }
    ));
  }

  #[Test]
  public function function_with_two_args_is_not_instance_of_zero_arg_function_type() {
    Assert::false((new FunctionType([], Type::$VAR))->isInstance(
      function($a, $b) { }
    ));
  }

  #[Test]
  public function function_with_zero_args_is_not_instance_of_two_arg_function_type() {
    Assert::false((new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->isInstance(
      function() { }
    ));
  }

  #[Test, Values(eval: '[[function() { }], [function($a) { }], [function($a, $b) { }], [function(array $a, callable $b, \lang\Value $c, $d, $e= false) { }]]')]
  public function function_with_no_args_is_instance_of_null_signature_function_type($value) {
    Assert::true((new FunctionType(null, Type::$VAR))->isInstance($value));
  }

  #[Test]
  public function builtin_strlen_isInstance() {
    Assert::true((new FunctionType([Primitive::$STRING], Primitive::$INT))->isInstance('strlen'));
  }

  #[Test]
  public function parameter_types_not_verified_for_builtin_strlen() {
    Assert::true((new FunctionType([Type::$VAR], Primitive::$INT))->isInstance('strlen'));
  }

  #[Test]
  public function return_type_not_verified_for_builtin_strlen() {
    Assert::true((new FunctionType([Primitive::$STRING], Type::$VAR))->isInstance('strlen'));
  }

  #[Test, Values([[['lang.XPClass', 'forName']], ['lang.XPClass::forName'], [[XPClass::class, 'forName']]])]
  public function array_referencing_static_class_method_is_instance($value) {
    $type= new FunctionType([Primitive::$STRING, XPClass::forName('lang.IClassLoader')], XPClass::forName('lang.XPClass'));
    Assert::true($type->isInstance($value));
  }

  #[Test, Values([[['lang.XPClass', 'forName']], ['lang.XPClass::forName'], [[XPClass::class, 'forName']]])]
  public function array_referencing_static_class_method_is_instance_without_optional_parameter($value) {
    $type= new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass'));
    Assert::true($type->isInstance($value));
  }

  #[Test, Values([[['lang.XPClass', 'forName']], ['lang.XPClass::forName'], [[XPClass::class, 'forName']]])]
  public function return_type_verified_for_static_class_methods($value) {
    $type= new FunctionType([Primitive::$STRING], Type::$VOID);
    Assert::false($type->isInstance($value));
  }

  #[Test, Values([[['lang.XPClass', 'forName']], ['lang.XPClass::forName'], [[XPClass::class, 'forName']]])]
  public function parameter_type_verified_for_static_class_methods($value) {
    $type= new FunctionType([XPClass::forName('lang.Value')], XPClass::forName('lang.XPClass'));
    Assert::false($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.Name', 'new']],  ['lang.unittest.Name::new'], [[Name::class, 'new']]])]
  public function array_referencing_constructor_is_instance($value) {
    $type= new FunctionType([Primitive::$STRING], XPClass::forName('lang.Value'));
    Assert::true($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.Nullable<int>', 'new']], ['lang.unittest.Nullable<int>::new']])]
  public function array_referencing_generic_constructor_is_instance($value) {
    $type= new FunctionType([], Type::forName('lang.unittest.Nullable<int>'));
    Assert::true($type->isInstance($value));
  }

  #[Test]
  public function array_referencing_non_existant_static_class_method_is_instance() {
    $type= new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass'));
    Assert::false($type->isInstance(['lang.XPClass', 'non-existant']));
  }

  #[Test, Values(eval: '[[Primitive::$STRING], [Type::$VAR]]')]
  public function array_referencing_instance_method_is_instance($return) {
    Assert::true((new FunctionType([], $return))->isInstance([$this, 'getName']));
  }

  #[Test]
  public function return_type_verified_for_instance_methods() {
    Assert::false((new FunctionType([], Primitive::$INT))->isInstance([$this, 'getName']));
  }

  #[Test]
  public function array_referencing_non_existant_instance_method_is_not_instance() {
    $type= new FunctionType([], Primitive::$STRING);
    Assert::false($type->isInstance([$this, 'non-existant']));
  }

  #[Test]
  public function lang_Type_forName_parsed_function_type() {
    Assert::equals(
      new FunctionType([Type::$VAR], Primitive::$BOOL),
      Type::forName('function(var): bool')
    );
  }

  #[Test]
  public function lang_Type_forName_parsed_wildcard_function_type() {
    Assert::equals(
      new FunctionType(null, Primitive::$BOOL),
      Type::forName('function(?): bool')
    );
  }

  #[Test]
  public function cast() {
    $value= function($a) { };
    Assert::equals($value, (new FunctionType([Type::$VAR], Type::$VAR))->cast($value));
  }

  #[Test]
  public function cast_null() {
    Assert::null((new FunctionType([Type::$VAR], Type::$VAR))->cast(null));
  }

  private function nonFunctions() {  
    yield [0];
    yield [-1];
    yield [0.5];
    yield [true];
    yield [false];
    yield [''];
    yield ['Test'];
    yield [[]];
    yield [['key' => 'value']];
    yield [['non-existant', 'method']];
    yield [['lang.XPClass', 'non-existant']];
    yield [[null, 'method']];
    yield [[new Name('test'), 'non-existant']];
  }

  #[Test, Expect(ClassCastException::class), Values(from: 'nonFunctions')]
  public function cannot_cast_this($value) {
    (new FunctionType([Type::$VAR], Type::$VAR))->cast($value);
  }

  #[Test, Expect(ClassCastException::class)]
  public function return_type_verified_for_instance_methods_when_casting() {
    (new FunctionType([], Primitive::$VOID))->cast([$this, 'getName']);
  }

  #[Test, Expect(ClassCastException::class)]
  public function number_of_required_parameters_is_verified_when_casting() {
    (new FunctionType([], Type::$VAR))->cast('strlen');
  }

  #[Test, Expect(ClassCastException::class)]
  public function excess_parameters_are_verified_when_casting() {
    (new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->cast('strlen');
  }

  #[Test]
  public function create_instances_from_function() {
    $value= (new FunctionType([], Type::$VAR))->newInstance(function() { return 'Test'; });
    Assert::equals('Test', $value());
  }

  #[Test, Condition(assert: 'fn() => !extension_loaded("xdebug")')]
  public function create_instances_from_string_referencing_builtin() {
    $value= (new FunctionType([Primitive::$STRING], Type::$VAR))->newInstance('strlen');
    Assert::equals(4, $value('Test'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function number_of_required_parameters_is_verified_when_creating_instances() {
    (new FunctionType([], Type::$VAR))->newInstance('strlen');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function excess_parameters_are_verified_when_creating_instances() {
    (new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->newInstance('strlen');
  }

  #[Test, Values(eval: '[[Primitive::$STRING], [Type::$VAR]]')]
  public function array_referencing_instance_method_works_for_newinstance($return) {
    (new FunctionType([], $return))->newInstance([$this, 'getName']);
  }

  #[Test, Values([[['lang.XPClass', 'forName']], ['lang.XPClass::forName'], [[XPClass::class, 'forName']]])]
  public function create_instances_from_array_referencing_static_class_method($value) {
    $value= (new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass')))->newInstance($value);
    Assert::equals(XPClass::forName('lang.unittest.Name'), $value('lang.unittest.Name'));
  }

  #[Test, Values([[['lang.unittest.Name', 'new']],  ['lang.unittest.Name::new'], [[Name::class, 'new']]])]
  public function create_instances_from_array_referencing_constructor($value) {
    $new= (new FunctionType([Primitive::$STRING], XPClass::forName('lang.unittest.Name')))->newInstance($value);
    Assert::instance(Name::class, $new('Test'));
  }

  #[Test, Values([[['lang.unittest.Name', 'new']], ['lang.unittest.Name::new'], [[Name::class, 'new']]])]
  public function create_instances_from_array_referencing_declared_constructor($value) {
    $new= (new FunctionType([Type::$VAR], XPClass::forName('lang.unittest.Name')))->newInstance($value);
    Assert::equals(new Name('test'), $new($this->getName()));
  }

  #[Test, Values([[['lang.unittest.Nullable<int>', 'new']], ['lang.unittest.Nullable<int>::new']])]
  public function create_instances_from_array_referencing_generic_constructor($value) {
    $new= (new FunctionType([Type::$VAR], Type::forName('lang.unittest.Nullable<int>')))->newInstance($value);
    Assert::instance('lang.unittest.Nullable<int>', $new());
  }

  #[Test, Expect(IllegalArgumentException::class), Values([[['lang.Value', 'new']], ['lang.Value::new']])]
  public function cannot_create_instances_from_interfaces($value) {
    (new FunctionType([Type::$VAR], Type::forName('lang.Value')))->newInstance($value);
  }

  #[Test, Expect(IllegalArgumentException::class), Values([[['lang.unittest.IDictionary<int, string>', 'new']], ['lang.unittest.IDictionary<int, string>::new']])]
  public function cannot_create_instances_from_generic_interfaces($value) {
    (new FunctionType([Type::$VAR], Type::forName('lang.unittest.IDictionary<int, string>')))->newInstance($value);
  }

  #[Test]
  public function create_instances_from_array_referencing_instance_method() {
    $value= (new FunctionType([], Primitive::$STRING))->newInstance([$this, 'getName']);
    Assert::equals($this->getName(), $value());
  }

  #[Test]
  public function create_instances_from_array_referencing_generic_instance_method() {
    $vector= create('new lang.unittest.ListOf<int>', 1, 2, 3);
    $value= (new FunctionType([], new ArrayType('int')))->newInstance([$vector, 'elements']);
    Assert::equals([1, 2, 3], $value());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function generic_argument_parameter_types_are_verified_when_creating_instances() {
    $vector= create('new lang.unittest.Nullable<int>');
    (new FunctionType([Primitive::$STRING], Primitive::$INT))->newInstance([$vector, 'add']);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function generic_argument_return_type_is_verified_when_creating_instances() {
    $vector= create('new lang.unittest.Nullable<int>');
    (new FunctionType([Primitive::$INT], Primitive::$STRING))->newInstance([$vector, 'add']);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function return_type_verified_for_instance_methods_when_creating_instances() {
    (new FunctionType([], Primitive::$VOID))->newInstance([$this, 'getName']);
  }

  #[Test, Expect(IllegalArgumentException::class), Values(from: 'nonFunctions')]
  public function cannot_create_instances_from($value) {
    (new FunctionType([], Type::$VAR))->newInstance($value);
  }

  #[Test]
  public function can_assign_to_itself() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    Assert::true($type->isAssignableFrom($type));
  }

  #[Test, Values(['var', 'string', 'function(): var', 'int[]', '[:bool]', 'lang.Value', 'lang.Type'])]
  public function var_return_type_is_assignable_from($return) {
    $type= new FunctionType([], Type::$VAR);
    Assert::true($type->isAssignableFrom(new FunctionType([], Type::forName($return))));
  }

  #[Test]
  public function var_return_type_not_assignable_from_void() {
    $type= new FunctionType([], Type::$VAR);
    Assert::false($type->isAssignableFrom(new FunctionType([], Type::$VOID)));
  }

  #[Test, Values(eval: '[[[]], [[Type::$VAR]], [[Type::$VAR, Type::$VAR]]]')]
  public function can_assign_to_wildcard_function($signature) {
    $type= new FunctionType(null, Type::$VAR);
    Assert::true($type->isAssignableFrom(new FunctionType($signature, Type::$VAR)));
  }

  #[Test]
  public function cannot_assign_if_number_of_arguments_smaller() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    Assert::false($type->isAssignableFrom(new FunctionType([], Type::$VAR)));
  }

  #[Test]
  public function cannot_assign_if_number_of_arguments_larger() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    Assert::false($type->isAssignableFrom(new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR)));
  }

  #[Test]
  public function cannot_assign_if_return_type_not_assignable() {
    $type= new FunctionType([], Primitive::$STRING);
    Assert::false($type->isAssignableFrom(new FunctionType([], Type::$VOID)));
  }

  #[Test]
  public function signature_matching() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    Assert::true($type->isAssignableFrom(new FunctionType([Primitive::$STRING], Type::$VAR)));
  }

  #[Test]
  public function invoke() {
    $f= function(Type $in) { return $in->getName(); };
    $t= new FunctionType([XPClass::forName('lang.Type')], Primitive::$STRING);
    Assert::equals('string', $t->invoke($f, [Primitive::$STRING]));
  }

  #[Test, Expect(IllegalArgumentException::class), Values(from: 'nonFunctions')]
  public function invoke_not_instance($value) {
    $t= new FunctionType([XPClass::forName('lang.Type')], Primitive::$STRING);
    $t->invoke($value);
  }

  #[Test, Expect(TargetInvocationException::class)]
  public function invoke_wraps_exceptions_in_TargetInvocationExceptions() {
    $t= new FunctionType([], Primitive::$VOID);
    $t->invoke(function() { throw new \lang\IllegalArgumentException('Test'); }, []);
  }

  #[Test]
  public function cast_loads_class_if_necessary_with_new() {
    $t= new FunctionType([Type::$VAR], Primitive::$VOID);
    $t->cast('lang.unittest.FunctionTypeFixture::new');
  }

  #[Test]
  public function cast_loads_class_if_necessary_with_method() {
    $t= new FunctionType([Type::$VAR], Primitive::$VOID);
    $t->cast('lang.unittest.FunctionTypeMethodFixture::method');
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_is_instance($value) {
    $type= new FunctionType([XPClass::forName('lang.unittest.FunctionTypeTest')], Primitive::$STRING);
    Assert::true($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_is_instance_with_optional_arg($value) {
    $type= new FunctionType([XPClass::forName('lang.unittest.FunctionTypeTest'), Primitive::$BOOL], Primitive::$STRING);
    Assert::true($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_is_not_instance_with_optional_arg_mismatch($value) {
    $type= new FunctionType([XPClass::forName('lang.unittest.FunctionTypeTest'), Primitive::$INT], Primitive::$STRING);
    Assert::false($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_is_instance_with_null_signature($value) {
    $type= new FunctionType(null, Primitive::$STRING);
    Assert::true($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_is_instance_with_exact_class($value) {
    $type= new FunctionType([XPClass::forName('lang.unittest.FunctionTypeTest')], Primitive::$STRING);
    Assert::true($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_is_instance_with_parent_class($value) {
    $type= new FunctionType([XPClass::forName('lang.unittest.BaseTest')], Primitive::$STRING);
    Assert::true($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_is_instance_with_var($value) {
    $type= new FunctionType([Type::$VAR], Primitive::$STRING);
    Assert::true($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_is_not_instance_with_class_mismatch($value) {
    $type= new FunctionType([XPClass::forName('lang.XPClass')], Primitive::$STRING);
    Assert::false($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_is_not_instance_without_class($value) {
    $type= new FunctionType([], Primitive::$STRING);
    Assert::false($type->isInstance($value));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_can_be_cast($value) {
    $type= new FunctionType([XPClass::forName('lang.unittest.FunctionTypeTest')], Primitive::$STRING);
    $f= $type->cast($value);
    Assert::equals($this->getName(), $f($this));
    Assert::equals($this->getName(true), $f($this, true));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_creating_new_instances($value) {
    $type= new FunctionType([XPClass::forName('lang.unittest.FunctionTypeTest')], Primitive::$STRING);
    $f= $type->newInstance($value);
    Assert::equals($this->getName(), $f($this));
    Assert::equals($this->getName(true), $f($this, true));
  }

  #[Test, Values([[['lang.unittest.FunctionTypeTest', 'getName']], ['lang.unittest.FunctionTypeTest::getName']])]
  public function reference_to_instance_method_can_be_invoked($value) {
    $type= new FunctionType([XPClass::forName('lang.unittest.FunctionTypeTest')], Primitive::$STRING);
    Assert::equals($this->getName(), $type->invoke($value, [$this]));
    Assert::equals($this->getName(true), $type->invoke($value, [$this, true]));
  }

  #[Test]
  public function invokeable_is_instance() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    Assert::true($type->isInstance(new FunctionTypeInvokeable()));
  }

  #[Test]
  public function casting_invokeable() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $inv= new FunctionTypeInvokeable();
    Assert::instance('Closure', $type->cast($inv));
  }

  #[Test]
  public function new_instance_of_invokeable() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $inv= new FunctionTypeInvokeable();
    Assert::instance('Closure', $type->newInstance($inv));
  }

  #[Test, Values(['function(?): var', 'function(var): var', 'function(var, var): var'])]
  public function var_arg_is_instance_of($type) {
    Assert::true(FunctionType::forName($type)->isInstance(function(... $args) { }));
  }

  #[Test, Values(['function(?): var', 'function(lang.Type): var', 'function(lang.Type, var): var', 'function(lang.Type, var, var): var'])]
  public function normal_and_var_arg_is_instance_of($type) {
    Assert::true(FunctionType::forName($type)->isInstance(function(Type $t, ... $args) { }));
  }
}