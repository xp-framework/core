<?php namespace net\xp_framework\unittest\core;

use net\xp_framework\unittest\Name;
use lang\Value;

/**
 * Tests the xp::stringOf() core utility
 *
 * @deprecated
 * @see   xp://net.xp_framework.unittest.core.NullTest
 * @see   https://github.com/xp-framework/xp-framework/issues/325
 */
class StringOfTest extends \unittest\TestCase {

  /**
   * Returns a class with a toString() method that always returns the 
   * string `TestString(6) { String }`.
   *
   * @return lang.Object
   */
  protected function testStringInstance() {
    return new class() implements Value {
      public function toString() { return 'TestString(6) { String }'; }
      public function hashCode() { return get_class($this); }
      public function compareTo($value) { return 1; }
    };
  }

  #[@test, @values([
  #  ['""', ''], ['"Hello"', 'Hello'],
  #  ['true', true], ['false', false],
  #  ['0', 0], ['1', 1], ['-1', -1],
  #  ['0', 0.0], ['1', 1.0], ['-1', -1.0], ['0.5', 0.5], ['-0.5', -0.5],
  #  ['null', null]
  #])]
  public function primitive_representation($expected, $value) {
    $this->assertEquals($expected, \xp::stringOf($value));
  }

  #[@test]
  public function testString_representation() {
    $this->assertEquals('TestString(6) { String }', \xp::stringOf($this->testStringInstance()));
  }

  #[@test]
  public function value_representation() {
    $this->assertEquals('value_representation', \xp::stringOf(new Name($this->name)));
  }

  #[@test]
  public function array_of_ints_representation() {
    $this->assertEquals(
      '[1, 2, 3]',
      \xp::stringOf([1, 2, 3])
    );
  }

  #[@test]
  public function array_of_array_of_ints_representation() {
    $this->assertEquals(
      '[[1, 2, 3]]',
      \xp::stringOf([[1, 2, 3]])
    );
  }

  #[@test]
  public function map_representation() {
    $this->assertEquals(
      "[\n  foo => \"bar\"\n  bar => 2\n  baz => TestString(6) { String }\n]", 
      \xp::stringOf([
        'foo' => 'bar', 
        'bar' => 2, 
        'baz' => $this->testStringInstance()
      ])
    );
  }

  #[@test]
  public function array_of_maps_representation() {
    $this->assertEquals(
      "[[\n  one => 1\n], [\n  two => 2\n]]",
      \xp::stringOf([['one' => 1], ['two' => 2]])
    );
  }

  #[@test]
  public function nested_arrays_and_maps() {
    $this->assertEquals(
      "[[\n  one => [[\n    one => 1\n  ]]\n], [\n  two => [[\n    two => 2\n  ]]\n]]",
      \xp::stringOf([['one' => [['one' => 1]]], ['two' => [['two' => 2]]]])
    );
  }

  #[@test]
  public function php_stdClass_representation() {
    $this->assertEquals("stdClass {\n}", \xp::stringOf(new \stdClass()));
  }

  #[@test]
  public function resource_representation() {
    $fd= fopen('php://stdin', 'r');
    $this->assertTrue((bool)preg_match('/resource\(type= stream, id= [0-9]+\)/', \xp::stringOf($fd)));
    fclose($fd);
  }

  #[@test]
  public function array_with_recursion_representation() {
    $a= [];
    $a[0]= 'Outer array';
    $a[1]= [];
    $a[1][0]= 'Inner array';
    $a[1][1]= &$a;
    $this->assertEquals('["Outer array", ["Inner array", ->{:recursion:}]]', \xp::stringOf($a));
  }

  #[@test]
  public function object_with_recursion_representation() {
    $o= new \stdClass();
    $o->child= new \stdClass();
    $o->child->parent= $o;
    $this->assertEquals('stdClass {
  child => stdClass {
    parent => ->{:recursion:}
  }
}',
    \xp::stringOf($o));
  }

  #[@test]
  public function twice_the_same_object_inside_array_not_recursion() {
    $test= new class() implements Value {
      public function toString() { return 'Test'; }
      public function hashCode() { return get_class($this); }
      public function compareTo($value) { return 1; }
    };
    $this->assertEquals(
      "[\n  a => Test\n  b => Test\n]", 
      \xp::stringOf(['a' => $test, 'b' => $test])
    );
  }
  
  #[@test]
  public function twice_the_same_object_with_huge_hashcode_inside_array_not_recursion() {
    $test= new class() implements Value {
      public function toString() { return 'Test'; }
      public function hashCode() { return 9E100; }
      public function compareTo($value) { return 1; }
    };
    $this->assertEquals(
      "[\n  a => Test\n  b => Test\n]", 
      \xp::stringOf(['a' => $test, 'b' => $test])
    );
  }

  #[@test]
  public function toString_calling_xp_stringOf_does_not_loop_forever() {
    $test= new class() implements Value {
      public function toString() { return \xp::stringOf($this); }
      public function hashCode() { return get_class($this); }
      public function compareTo($value) { return 1; }
    };
    $this->assertEquals(
      nameof($test)." {\n}",
      \xp::stringOf($test)
    );
  }
  
  #[@test]
  public function repeated_calls_to_xp_stringOf_yield_same_result() {
    $object= $this->testStringInstance();
    $stringRep= $object->toString();
    $this->assertEquals(
      [$stringRep, $stringRep],
      [\xp::stringOf($object), \xp::stringOf($object)]
    );
  }

  #[@test]
  public function indenting() {
    $cl= \lang\ClassLoader::defineClass('net.xp_framework.unittest.core.StringOfTest_IndentingFixture', null, ['lang.Value'], '{
      protected $inner= null;
      public function __construct($inner) {
        $this->inner= $inner;
      }
      public function hashCode() {
        return "test";
      }
      public function toString() {
        return "object {\n  ".\xp::stringOf($this->inner, "  ")."\n}";
      }
      public function compareTo($value) {
        return 1; 
      }
    }');
    $this->assertEquals(
      "object {\n  object {\n    null\n  }\n}",
      $cl->newInstance($cl->newInstance(null))->toString()
    );
  }

  #[@test]
  public function closure() {
    $this->assertEquals('<function()>', \xp::stringOf(function() { }));
  }

  #[@test]
  public function closure_parameter_is_printed() {
    $this->assertEquals('<function($a)>', \xp::stringOf(function($a) { }));
  }

  #[@test]
  public function closure_parameters_are_printed() {
    $this->assertEquals('<function($a, $b)>', \xp::stringOf(function($a, $b) { }));
  }

  #[@test]
  public function closure_inside_object_does_not_raise_serialization_exception() {
    $instance= new class(function($a, $b) { }) {
      public $closure = null;
      public function __construct($closure) { $this->closure= $closure; }
    };
    \xp::stringOf($instance);
  }

  #[@test]
  public function closure_inside_array_does_not_raise_serialization_exception() {
    \xp::stringOf([function($a, $b) { }]);
  }
}
