<?php namespace net\xp_framework\unittest\core;

use util\collections\Vector;

/**
 * Tests the is() core functionality
 *
 * @see      php://is_a
 */
class IsTest extends \unittest\TestCase {

  /** @deprecated */
  #[@test]
  public function xpnullIsnull() {
    $this->assertTrue(is(null, \xp::null()));
  }

  #[@test]
  public function intIsNotIsnull() {
    $this->assertFalse(is(null, 1));
  }

  #[@test]
  public function string_array() {
    $this->assertTrue(is('string[]', ['Hello']));
  }

  #[@test]
  public function var_array() {
    $this->assertFalse(is('string[]', ['Hello', 1, true]));
  }

  #[@test]
  public function int_array() {
    $this->assertTrue(is('int[]', [1, 2, 3]));
  }

  #[@test]
  public function mapIsNotAnInt_array() {
    $this->assertFalse(is('int[]', ['one' => 1, 'two' => 2]));
  }

  #[@test]
  public function intIsNotAnInt_array() {
    $this->assertFalse(is('int[]', 1));
  }

  #[@test]
  public function thisIsNotAnInt_array() {
    $this->assertFalse(is('int[]', $this));
  }

  #[@test]
  public function emptyArrayIsAnInt_array() {
    $this->assertTrue(is('int[]', []));
  }

  #[@test]
  public function object_array() {
    $this->assertTrue(is('lang.Object[]', [new \lang\Object(), new \lang\Object(), new \lang\Object()]));
  }

  #[@test]
  public function objectArrayWithnull() {
    $this->assertFalse(is('lang.Object[]', [new \lang\Object(), new \lang\Object(), null]));
  }

  #[@test]
  public function stringMap() {
    $this->assertTrue(is('[:string]', ['greet' => 'Hello', 'whom' => 'World']));
  }

  #[@test]
  public function intMap() {
    $this->assertTrue(is('[:int]', ['greet' => 1, 'whom' => 2]));
  }

  #[@test]
  public function intArrayIsNotAnIntMap() {
    $this->assertFalse(is('[:int]', [1, 2, 3]));
  }

  #[@test]
  public function intIsNotAnIntMap() {
    $this->assertFalse(is('[:int]', 1));
  }

  #[@test]
  public function thisIsNotAnIntMap() {
    $this->assertFalse(is('[:int]', $this));
  }

  #[@test]
  public function emptyArrayIsAnIntMap() {
    $this->assertTrue(is('[:int]', []));
  }

  #[@test]
  public function stringPrimitive() {
    $this->assertTrue(is('string', 'Hello'));
  }

  #[@test]
  public function nullNotAStringPrimitive() {
    $this->assertFalse(is('string', null));
  }

  #[@test]
  public function boolPrimitive() {
    $this->assertTrue(is('bool', true));
  }

  #[@test]
  public function nullNotABoolPrimitive() {
    $this->assertFalse(is('bool', null));
  }

  #[@test]
  public function doublePrimitive() {
    $this->assertTrue(is('double', 0.0));
  }

  #[@test]
  public function nullNotADoublePrimitive() {
    $this->assertFalse(is('double', null));
  }

  #[@test]
  public function intPrimitive() {
    $this->assertTrue(is('int', 0));
  }

  #[@test]
  public function nullNotAnIntPrimitive() {
    $this->assertFalse(is('int', null));
  }

  #[@test]
  public function shortClassName() {
    $this->assertTrue(is('Generic', new \lang\Object()));
  }

  #[@test]
  public function undefinedClassName() {
    $this->assertFalse(class_exists('Undefined_Class', false));
    $this->assertFalse(is('Undefined_Class', new \lang\Object()));
  }

  #[@test]
  public function fullyQualifiedClassName() {
    $this->assertTrue(is('lang.Generic', new \lang\Object()));
  }

  #[@test]
  public function interfaces() {
    \lang\ClassLoader::defineClass(
      'net.xp_framework.unittest.core.RunnableImpl', 
      'lang.Object',
      ['lang.Runnable'],
      ['run' => function() { }]
    );
    \lang\ClassLoader::defineClass(
      'net.xp_framework.unittest.core.RunnableImplEx', 
      'net.xp_framework.unittest.core.RunnableImpl',
      [],
      []
    );
    
    $this->assertTrue(is('lang.Runnable', new RunnableImpl()));
    $this->assertTrue(is('lang.Runnable', new RunnableImplEx()));
    $this->assertFalse(is('lang.Runnable', new \lang\Object()));
  }

  #[@test]
  public function aStringVectorIsIsItself() {
    $this->assertTrue(is('util.collections.Vector<string>', create('new util.collections.Vector<string>')));
  }

  #[@test]
  public function aVectorIsNotAStringVector() {
    $this->assertFalse(is('util.collections.Vector<string>', new Vector()));
  }

  #[@test]
  public function aStringVectorIsNotAVector() {
    $this->assertFalse(is(
      'util.collections.Vector',
      create('new util.collections.Vector<string>')
    ));
  }

  #[@test]
  public function anIntVectorIsNotAStringVector() {
    $this->assertFalse(is(
      'util.collections.Vector<string>',
      create('new util.collections.Vector<int>')
    ));
  }

  #[@test]
  public function aVectorOfIntVectorsIsItself() {
    $this->assertTrue(is(
      'util.collections.Vector<util.collections.Vector<int>>',
      create('new util.collections.Vector<util.collections.Vector<int>>')
    ));
  }

  #[@test]
  public function aVectorOfIntVectorsIsNotAVectorOfStringVectors() {
    $this->assertFalse(is(
      'util.collections.Vector<Vector<string>>',
      create('new util.collections.Vector<util.collections.Vector<int>>')
    ));
  }
 
  #[@test]
  public function anIntVectorIsNotAnUndefinedGeneric() {
    $this->assertFalse(is('Undefined_Class<string>', create('new util.collections.Vector<int>')));
  }

  /** @return var[][] */
  protected function genericVectors() {
    return [
      [create('new util.collections.Vector<string>')],
      [create('new util.collections.Vector<lang.Generic>')],
      [create('new util.collections.Vector<util.collections.Vector<int>>')],
    ];
  }

  #[@test, @values('genericVectors')]
  public function wildcard_check_for_single_type_parameter($value) {
    $this->assertTrue(is('util.collections.Vector<?>', $value));
  }

  #[@test, @values('genericVectors')]
  public function wildcard_check_for_single_type_parameter_super_type($value) {
    $this->assertTrue(is('util.collections.IList<?>', $value));
  }

  #[@test]
  public function wildcard_check_for_single_type_parameter_generic() {
    $this->assertTrue(is('util.collections.IList<util.collections.IList<?>>', create('new util.collections.Vector<util.collections.Vector<int>>')));
  }

  #[@test]
  public function wildcard_check_for_type_parameters() {
    $this->assertTrue(is('util.collections.HashTable<?, ?>', create('new util.collections.HashTable<string, lang.Generic>')));
  }

  #[@test]
  public function wildcard_check_for_type_parameters_super_type() {
    $this->assertTrue(is('util.collections.Map<?, ?>', create('new util.collections.HashTable<string, lang.Generic>')));
  }

  #[@test]
  public function wildcard_check_for_type_parameters_partial() {
    $this->assertTrue(is('util.collections.HashTable<string, ?>', create('new util.collections.HashTable<string, lang.Generic>')));
  }

  #[@test]
  public function wildcard_check_for_newinstance() {
    $this->assertTrue(is('util.Filter<?>', newinstance('util.Filter<string>', [], [
      'accept' => function($e) { return true; }
    ])));
  }

  #[@test]
  public function function_type() {
    $this->assertTrue(is('function(): var', function() { }));
  }

  #[@test]
  public function function_type_returning_array() {
    $this->assertTrue(is('function(): var[]', function() { }));
  }

  #[@test]
  public function braced_function_type() {
    $this->assertTrue(is('(function(): var)', function() { }));
  }

  #[@test]
  public function array_of_function_type() {
    $this->assertTrue(is('(function(): var)[]', [function() { }]));
  }
}
