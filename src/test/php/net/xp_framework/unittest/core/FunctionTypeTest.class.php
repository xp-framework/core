<?php namespace net\xp_framework\unittest\core;

use lang\FunctionType;
use lang\Primitive;
use lang\Object;
use lang\XPClass;
use lang\Type;
use lang\ArrayType;
use lang\MapType;
use lang\ClassCastException;
use lang\IllegalArgumentException;
use lang\reflect\TargetInvocationException;
use unittest\TestCase;

class FunctionTypeTest extends TestCase {

  #[@test]
  public function can_create_with_type_instances() {
    new FunctionType([Primitive::$STRING], Primitive::$STRING);
  }

  #[@test]
  public function can_create_with_type_names() {
    new FunctionType(['string'], 'string');
  }

  #[@test]
  public function returns() {
    $this->assertEquals(Type::$VOID, (new FunctionType([Primitive::$STRING], Type::$VOID))->returns());
  }

  #[@test]
  public function signature() {
    $this->assertEquals([Primitive::$STRING], (new FunctionType([Primitive::$STRING], Type::$VOID))->signature());
  }

  #[@test]
  public function a_function_accepting_one_string_arg_and_returning_a_string() {
    $this->assertEquals(
      new FunctionType([Primitive::$STRING], Primitive::$STRING),
      FunctionType::forName('function(string): string')
    );
  }

  #[@test]
  public function a_function_accepting_two_string_args_and_returning_a_string() {
    $this->assertEquals(
      new FunctionType([Primitive::$STRING, Primitive::$STRING], Primitive::$STRING),
      FunctionType::forName('function(string, string): string')
    );
  }

  #[@test]
  public function a_zero_arg_function_which_returns_bool() {
    $this->assertEquals(
      new FunctionType([], Primitive::$BOOL),
      FunctionType::forName('function(): bool')
    );
  }

  #[@test]
  public function a_zero_arg_function_which_returns_a_function_type() {
    $this->assertEquals(
      new FunctionType([], new FunctionType([Primitive::$STRING], Primitive::$INT)),
      FunctionType::forName('function(): function(string): int')
    );
  }

  #[@test]
  public function a_function_which_accepts_a_function_type() {
    $this->assertEquals(
      new FunctionType([new FunctionType([Primitive::$STRING], Primitive::$INT)], Type::$VAR),
      FunctionType::forName('function(function(string): int): var')
    );
  }

  #[@test]
  public function a_function_accepting_an_array_of_generic_objects_and_not_returning_anything() {
    $this->assertEquals(
      new FunctionType([new ArrayType(XPClass::forName('lang.Generic'))], Type::$VOID),
      FunctionType::forName('function(lang.Generic[]): void')
    );
  }

  #[@test]
  public function function_with_zero_args_is_instance_of_zero_arg_function_type() {
    $this->assertTrue((new FunctionType([], Type::$VAR))->isInstance(
      function() { }
    ));
  }

  #[@test]
  public function function_with_two_args_is_instance_of_two_arg_function_type() {
    $this->assertTrue((new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->isInstance(
      function($a, $b) { }
    ));
  }

  #[@test]
  public function function_with_type_hinted_arg_is_instance_of_function_type_with_class_signature() {
    $this->assertTrue((new FunctionType([XPClass::forName('lang.XPClass')], Type::$VAR))->isInstance(
      function(XPClass $c) { }
    ));
  }

  #[@test]
  public function function_with_array_hinted_arg_is_instance_of_function_type_with_array_signature() {
    $this->assertTrue((new FunctionType([Type::$ARRAY], Type::$VAR))->isInstance(
      function(array $a) { }
    ));
  }

  #[@test]
  public function function_with_callable_hinted_arg_is_instance_of_function_type_with_function_signature() {
    $this->assertTrue((new FunctionType([new FunctionType(null, Type::$VAR)], Type::$VAR))->isInstance(
      function(callable $a) { }
    ));
  }

  #[@test]
  public function function_with_two_args_is_not_instance_of_zero_arg_function_type() {
    $this->assertFalse((new FunctionType([], Type::$VAR))->isInstance(
      function($a, $b) { }
    ));
  }

  #[@test]
  public function function_with_zero_args_is_not_instance_of_two_arg_function_type() {
    $this->assertFalse((new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->isInstance(
      function() { }
    ));
  }

  #[@test, @values([
  #  [function() { }],
  #  [function($a) { }],
  #  [function($a, $b) { }],
  #  [function(array $a, callable $b, \lang\Object $c, $d, $e= false) { }]
  #])]
  public function function_with_no_args_is_instance_of_null_signature_function_type($value) {
    $this->assertTrue((new FunctionType(null, Type::$VAR))->isInstance($value));
  }

  #[@test]
  public function builtin_strlen_isInstance() {
    $this->assertTrue((new FunctionType([Primitive::$STRING], Primitive::$INT))->isInstance('strlen'));
  }

  #[@test]
  public function parameter_types_not_verified_for_builtin_strlen() {
    $this->assertTrue((new FunctionType([Type::$VAR], Primitive::$INT))->isInstance('strlen'));
  }

  #[@test]
  public function return_type_not_verified_for_builtin_strlen() {
    $this->assertTrue((new FunctionType([Primitive::$STRING], Type::$VAR))->isInstance('strlen'));
  }

  #[@test, @values([
  #  [['lang.XPClass', 'forName']], ['lang.XPClass::forName'],
  #  [[XPClass::class, 'forName']]
  #])]
  public function array_referencing_static_class_method_is_instance($value) {
    $type= new FunctionType([Primitive::$STRING, XPClass::forName('lang.IClassLoader')], XPClass::forName('lang.XPClass'));
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['lang.XPClass', 'forName']], ['lang.XPClass::forName'],
  #  [[XPClass::class, 'forName']]
  #])]
  public function array_referencing_static_class_method_is_instance_without_optional_parameter($value) {
    $type= new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass'));
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['lang.XPClass', 'forName']], ['lang.XPClass::forName'],
  #  [[XPClass::class, 'forName']]
  #])]
  public function return_type_verified_for_static_class_methods($value) {
    $type= new FunctionType([Primitive::$STRING], Type::$VOID);
    $this->assertFalse($type->isInstance($value));
  }

  #[@test, @values([
  #  [['lang.XPClass', 'forName']], ['lang.XPClass::forName'],
  #  [[XPClass::class, 'forName']]
  #])]
  public function parameter_type_verified_for_static_class_methods($value) {
    $type= new FunctionType([XPClass::forName('lang.Object')], XPClass::forName('lang.XPClass'));
    $this->assertFalse($type->isInstance($value));
  }

  #[@test, @values([
  #  [['lang.Object', 'new']], ['lang.Object::new'],
  #  [[Object::class, 'new']]
  #])]
  public function array_referencing_constructor_is_instance($value) {
    $type= new FunctionType([], XPClass::forName('lang.Object'));
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([[['net.xp_framework.unittest.core.generics.Nullable<int>', 'new']], ['net.xp_framework.unittest.core.generics.Nullable<int>::new']])]
  public function array_referencing_generic_constructor_is_instance($value) {
    $type= new FunctionType([], Type::forName('net.xp_framework.unittest.core.generics.Nullable<int>'));
    $this->assertTrue($type->isInstance($value));
  }

  #[@test]
  public function array_referencing_non_existant_static_class_method_is_instance() {
    $type= new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass'));
    $this->assertFalse($type->isInstance(['lang.XPClass', 'non-existant']));
  }

  #[@test, @values([[Primitive::$STRING], [Type::$VAR]])]
  public function array_referencing_instance_method_is_instance($return) {
    $this->assertTrue((new FunctionType([], $return))->isInstance([$this, 'getName']));
  }

  #[@test]
  public function return_type_verified_for_instance_methods() {
    $this->assertFalse((new FunctionType([], Primitive::$INT))->isInstance([$this, 'getName']));
  }

  #[@test]
  public function array_referencing_non_existant_instance_method_is_not_instance() {
    $type= new FunctionType([], Primitive::$STRING);
    $this->assertFalse($type->isInstance([$this, 'non-existant']));
  }

  #[@test]
  public function lang_Type_forName_parsed_function_type() {
    $this->assertEquals(
      new FunctionType([Type::$VAR], Primitive::$BOOL),
      Type::forName('function(var): bool')
    );
  }

  #[@test]
  public function lang_Type_forName_parsed_wildcard_function_type() {
    $this->assertEquals(
      new FunctionType(null, Primitive::$BOOL),
      Type::forName('function(?): bool')
    );
  }

  #[@test]
  public function cast() {
    $value= function($a) { };
    $this->assertEquals($value, (new FunctionType([Type::$VAR], Type::$VAR))->cast($value));
  }

  #[@test]
  public function cast_null() {
    $this->assertNull((new FunctionType([Type::$VAR], Type::$VAR))->cast(null));
  }

  #[@test, @expect(ClassCastException::class), @values([
  #  0, -1, 0.5, true, false, '', 'Test',
  #  [[]], [['key' => 'value']],
  #  [['non-existant', 'method']], [['lang.XPClass', 'non-existant']],
  #  [[null, 'method']], [[new \lang\Object(), 'non-existant']]
  #])]
  public function cannot_cast_this($value) {
    (new FunctionType([Type::$VAR], Type::$VAR))->cast($value);
  }

  #[@test, @expect(ClassCastException::class)]
  public function return_type_verified_for_instance_methods_when_casting() {
    (new FunctionType([], Primitive::$VOID))->cast([$this, 'getName']);
  }

  #[@test, @expect(ClassCastException::class)]
  public function number_of_required_parameters_is_verified_when_casting() {
    (new FunctionType([], Type::$VAR))->cast('strlen');
  }

  #[@test, @expect(ClassCastException::class)]
  public function excess_parameters_are_verified_when_casting() {
    (new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->cast('strlen');
  }

  #[@test]
  public function create_instances_from_function() {
    $value= (new FunctionType([], Type::$VAR))->newInstance(function() { return 'Test'; });
    $this->assertEquals('Test', $value());
  }

  #[@test]
  public function create_instances_from_string_referencing_builtin() {
    $value= (new FunctionType([Primitive::$STRING], Type::$VAR))->newInstance('strlen');
    $this->assertEquals(4, $value('Test'));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function number_of_required_parameters_is_verified_when_creating_instances() {
    (new FunctionType([], Type::$VAR))->newInstance('strlen');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function excess_parameters_are_verified_when_creating_instances() {
    (new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR))->newInstance('strlen');
  }

  #[@test, @values([[Primitive::$STRING], [Type::$VAR]])]
  public function array_referencing_instance_method_works_for_newinstance($return) {
    (new FunctionType([], $return))->newInstance([$this, 'getName']);
  }

  #[@test, @values([
  #  [['lang.XPClass', 'forName']], ['lang.XPClass::forName'],
  #  [[XPClass::class, 'forName']]
  #])]
  public function create_instances_from_array_referencing_static_class_method($value) {
    $value= (new FunctionType([Primitive::$STRING], XPClass::forName('lang.XPClass')))->newInstance($value);
    $this->assertEquals(XPClass::forName('lang.Object'), $value('lang.Object'));
  }

  #[@test, @values([
  #  [['lang.Object', 'new']], ['lang.Object::new'],
  #  [[Object::class, 'new']]
  #])]
  public function create_instances_from_array_referencing_constructor($value) {
    $new= (new FunctionType([], XPClass::forName('lang.Object')))->newInstance($value);
    $this->assertInstanceOf(Object::class, $new());
  }

  #[@test, @values([
  #  [['unittest.TestCase', 'new']], ['unittest.TestCase::new'],
  #  [[TestCase::class, 'new']]
  #])]
  public function create_instances_from_array_referencing_declared_constructor($value) {
    $new= (new FunctionType([Type::$VAR], XPClass::forName('unittest.TestCase')))->newInstance($value);
    $this->assertEquals($this, $new($this->getName()));
  }

  #[@test, @values([[['net.xp_framework.unittest.core.generics.Nullable<int>', 'new']], ['net.xp_framework.unittest.core.generics.Nullable<int>::new']])]
  public function create_instances_from_array_referencing_generic_constructor($value) {
    $new= (new FunctionType([Type::$VAR], Type::forName('net.xp_framework.unittest.core.generics.Nullable<int>')))->newInstance($value);
    $this->assertInstanceOf('net.xp_framework.unittest.core.generics.Nullable<int>', $new());
  }

  #[@test, @expect(IllegalArgumentException::class), @values([[['lang.Generic', 'new']], ['lang.Generic::new']])]
  public function cannot_create_instances_from_interfaces($value) {
    (new FunctionType([Type::$VAR], Type::forName('lang.Generic')))->newInstance($value);
  }

  #[@test, @expect(IllegalArgumentException::class), @values([[['net.xp_framework.unittest.core.generics.IDictionary<int, string>', 'new']], ['net.xp_framework.unittest.core.generics.IDictionary<int, string>::new']])]
  public function cannot_create_instances_from_generic_interfaces($value) {
    (new FunctionType([Type::$VAR], Type::forName('net.xp_framework.unittest.core.generics.IDictionary<int, string>')))->newInstance($value);
  }

  #[@test]
  public function create_instances_from_array_referencing_instance_method() {
    $value= (new FunctionType([], Primitive::$STRING))->newInstance([$this, 'getName']);
    $this->assertEquals($this->getName(), $value());
  }

  #[@test]
  public function create_instances_from_array_referencing_generic_instance_method() {
    $vector= create('new net.xp_framework.unittest.core.generics.ListOf<int>', 1, 2, 3);
    $value= (new FunctionType([], new ArrayType('int')))->newInstance([$vector, 'elements']);
    $this->assertEquals([1, 2, 3], $value());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function generic_argument_parameter_types_are_verified_when_creating_instances() {
    $vector= create('new net.xp_framework.unittest.core.generics.Nullable<int>');
    (new FunctionType([Primitive::$STRING], Primitive::$INT))->newInstance([$vector, 'add']);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function generic_argument_return_type_is_verified_when_creating_instances() {
    $vector= create('new net.xp_framework.unittest.core.generics.Nullable<int>');
    (new FunctionType([Primitive::$INT], Primitive::$STRING))->newInstance([$vector, 'add']);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function return_type_verified_for_instance_methods_when_creating_instances() {
    (new FunctionType([], Primitive::$VOID))->newInstance([$this, 'getName']);
  }

  #[@test, @expect(IllegalArgumentException::class), @values([
  #  null,
  #  0, -1, 0.5, true, false, '', 'Test',
  #  [[]], [['key' => 'value']],
  #  [['non-existant', 'method']], [['lang.XPClass', 'non-existant']],
  #  [[null, 'method']], [[new \lang\Object(), 'non-existant']]
  #])]
  public function cannot_create_instances_from($value) {
    (new FunctionType([], Type::$VAR))->newInstance($value);
  }

  #[@test]
  public function can_assign_to_itself() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertTrue($type->isAssignableFrom($type));
  }

  #[@test, @values(['var', 'string', 'function(): var', 'int[]', '[:bool]', 'lang.Generic', 'lang.Object'])]
  public function var_return_type_is_assignable_from($return) {
    $type= new FunctionType([], Type::$VAR);
    $this->assertTrue($type->isAssignableFrom(new FunctionType([], Type::forName($return))));
  }

  #[@test]
  public function var_return_type_not_assignable_from_void() {
    $type= new FunctionType([], Type::$VAR);
    $this->assertFalse($type->isAssignableFrom(new FunctionType([], Type::$VOID)));
  }

  #[@test, @values([
  #  [[]],
  #  [[Type::$VAR]],
  #  [[Type::$VAR, Type::$VAR]]
  #])]
  public function can_assign_to_wildcard_function($signature) {
    $type= new FunctionType(null, Type::$VAR);
    $this->assertTrue($type->isAssignableFrom(new FunctionType($signature, Type::$VAR)));
  }

  #[@test]
  public function cannot_assign_if_number_of_arguments_smaller() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertFalse($type->isAssignableFrom(new FunctionType([], Type::$VAR)));
  }

  #[@test]
  public function cannot_assign_if_number_of_arguments_larger() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertFalse($type->isAssignableFrom(new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR)));
  }

  #[@test]
  public function cannot_assign_if_return_type_not_assignable() {
    $type= new FunctionType([], Primitive::$STRING);
    $this->assertFalse($type->isAssignableFrom(new FunctionType([], Type::$VOID)));
  }

  #[@test]
  public function signature_matching() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertTrue($type->isAssignableFrom(new FunctionType([Primitive::$STRING], Type::$VAR)));
  }

  #[@test]
  public function invoke() {
    $f= function(Type $in) { return $in->getName(); };
    $t= new FunctionType([XPClass::forName('lang.Type')], Primitive::$STRING);
    $this->assertEquals('string', $t->invoke($f, [Primitive::$STRING]));
  }

  #[@test, @expect(IllegalArgumentException::class), @values([
  #  null,
  #  0, -1, 0.5, true, false, '', 'Test',
  #  [[]], [['key' => 'value']],
  #  [['non-existant', 'method']], [['lang.XPClass', 'non-existant']],
  #  [[null, 'method']], [[new \lang\Object(), 'non-existant']],
  #  function() { }
  #])]
  public function invoke_not_instance($value) {
    $t= new FunctionType([XPClass::forName('lang.Type')], Primitive::$STRING);
    $t->invoke($value);
  }

  #[@test, @expect(TargetInvocationException::class)]
  public function invoke_wraps_exceptions_in_TargetInvocationExceptions() {
    $t= new FunctionType([], Primitive::$VOID);
    $t->invoke(function() { throw new \lang\IllegalArgumentException('Test'); }, []);
  }

  #[@test]
  public function invoke_does_not_wrap_SystemExit() {
    $t= new FunctionType([], Primitive::$VOID);
    try {
      $t->invoke(function() { throw new \lang\SystemExit(0); }, []);
      $this->fail('No exception thrown', null, 'lang.SystemExit');
    } catch (\lang\SystemExit $expected) {
      // OK
    }
  }

  #[@test]
  public function cast_loads_class_if_necessary_with_new() {
    $t= new FunctionType([Type::$VAR], Primitive::$VOID);
    $t->cast('net.xp_framework.unittest.core.FunctionTypeFixture::new');
  }

  #[@test]
  public function cast_loads_class_if_necessary_with_method() {
    $t= new FunctionType([Type::$VAR], Primitive::$VOID);
    $t->cast('net.xp_framework.unittest.core.FunctionTypeMethodFixture::method');
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_is_instance($value) {
    $type= new FunctionType([XPClass::forName('unittest.TestCase')], Primitive::$STRING);
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_is_instance_with_optional_arg($value) {
    $type= new FunctionType([XPClass::forName('unittest.TestCase'), Primitive::$BOOL], Primitive::$STRING);
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_is_not_instance_with_optional_arg_mismatch($value) {
    $type= new FunctionType([XPClass::forName('unittest.TestCase'), Primitive::$INT], Primitive::$STRING);
    $this->assertFalse($type->isInstance($value));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_is_instance_with_null_signature($value) {
    $type= new FunctionType(null, Primitive::$STRING);
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_is_instance_with_exact_class($value) {
    $type= new FunctionType([XPClass::forName('net.xp_framework.unittest.core.FunctionTypeTest')], Primitive::$STRING);
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_is_instance_with_parent_class($value) {
    $type= new FunctionType([XPClass::forName('lang.Object')], Primitive::$STRING);
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_is_instance_with_interface($value) {
    $type= new FunctionType([XPClass::forName('lang.Generic')], Primitive::$STRING);
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_is_instance_with_var($value) {
    $type= new FunctionType([Type::$VAR], Primitive::$STRING);
    $this->assertTrue($type->isInstance($value));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_is_not_instance_with_class_mismatch($value) {
    $type= new FunctionType([XPClass::forName('lang.XPClass')], Primitive::$STRING);
    $this->assertFalse($type->isInstance($value));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_is_not_instance_without_class($value) {
    $type= new FunctionType([], Primitive::$STRING);
    $this->assertFalse($type->isInstance($value));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_can_be_cast($value) {
    $type= new FunctionType([XPClass::forName('unittest.TestCase')], Primitive::$STRING);
    $f= $type->cast($value);
    $this->assertEquals($this->getName(), $f($this));
    $this->assertEquals($this->getName(true), $f($this, true));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_creating_new_instances($value) {
    $type= new FunctionType([XPClass::forName('unittest.TestCase')], Primitive::$STRING);
    $f= $type->newInstance($value);
    $this->assertEquals($this->getName(), $f($this));
    $this->assertEquals($this->getName(true), $f($this, true));
  }

  #[@test, @values([
  #  [['net.xp_framework.unittest.core.FunctionTypeTest', 'getName']],
  #  ['net.xp_framework.unittest.core.FunctionTypeTest::getName']
  #])]
  public function reference_to_instance_method_can_be_invoked($value) {
    $type= new FunctionType([XPClass::forName('unittest.TestCase')], Primitive::$STRING);
    $this->assertEquals($this->getName(), $type->invoke($value, [$this]));
    $this->assertEquals($this->getName(true), $type->invoke($value, [$this, true]));
  }

  #[@test]
  public function invokeable_is_instance() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $this->assertTrue($type->isInstance(new FunctionTypeInvokeable()));
  }

  #[@test]
  public function casting_invokeable() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $inv= new FunctionTypeInvokeable();
    $this->assertInstanceOf('Closure', $type->cast($inv));
  }

  #[@test]
  public function new_instance_of_invokeable() {
    $type= new FunctionType([Type::$VAR], Type::$VAR);
    $inv= new FunctionTypeInvokeable();
    $this->assertInstanceOf('Closure', $type->newInstance($inv));
  }

  #[@test, @values([
  #  'function(?): var',
  #  'function(var): var',
  #  'function(var, var): var'
  #])]
  public function var_arg_is_instance_of($type) {
    $this->assertTrue(FunctionType::forName($type)->isInstance(function(... $args) { }));
  }

  #[@test, @values([
  #  'function(?): var',
  #  'function(lang.Type): var',
  #  'function(lang.Type, var): var',
  #  'function(lang.Type, var, var): var'
  #])]
  public function normal_and_var_arg_is_instance_of($type) {
    $this->assertTrue(FunctionType::forName($type)->isInstance(function(Type $t, ... $args) { }));
  }
}
