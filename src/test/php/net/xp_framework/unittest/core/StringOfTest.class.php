<?php namespace net\xp_framework\unittest\core;

/**
 * Tests the xp::stringOf() core utility
 *
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
    return newinstance('lang.Object', [], array(
      'toString' => function($self) { return 'TestString(6) { String }'; }
    ));
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
  public function xpnull_representation() {
    $this->assertEquals('<null>', \xp::stringOf(\xp::null()));
  }

  #[@test]
  public function testString_representation() {
    $this->assertEquals('TestString(6) { String }', \xp::stringOf($this->testStringInstance()));
  }

  #[@test]
  public function array_of_ints_representation() {
    $this->assertEquals(
      "[\n  0 => 1\n  1 => 2\n  2 => 3\n]", 
      \xp::stringOf(array(1, 2, 3))
    );
  }

  #[@test]
  public function array_of_array_of_ints_representation() {
    $this->assertEquals(
      "[\n  0 => [\n    0 => 1\n    1 => 2\n    2 => 3\n  ]\n]", 
      \xp::stringOf(array(array(1, 2, 3)))
    );
  }

  #[@test]
  public function hashmap_representation() {
    $this->assertEquals(
      "[\n  foo => \"bar\"\n  bar => 2\n  baz => TestString(6) { String }\n]", 
      \xp::stringOf(array(
        'foo' => 'bar', 
        'bar' => 2, 
        'baz' => $this->testStringInstance()
      ))
    );
  }

  #[@test]
  public function php_stdClass_representation() {
    $this->assertEquals("php.stdClass {\n}", \xp::stringOf(new \stdClass()));
  }

  #[@test]
  public function php_Directory_representation() {
    $this->assertEquals("php.Directory {\n}", \xp::stringOf(new \Directory('.')));
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
    $this->assertEquals('[
  0 => "Outer array"
  1 => [
    0 => "Inner array"
    1 => ->{:recursion:}
  ]
]', 
    \xp::stringOf($a));
  }

  #[@test]
  public function object_with_recursion_representation() {
    $o= new \stdClass();
    $o->child= new \stdClass();
    $o->child->parent= $o;
    $this->assertEquals('php.stdClass {
  child => php.stdClass {
    parent => ->{:recursion:}
  }
}',
    \xp::stringOf($o));
  }

  #[@test]
  public function twice_the_same_object_inside_array_not_recursion() {
    $test= newinstance('lang.Object', [], array(
      'toString' => function($self) { return 'Test'; }
    ));
    $this->assertEquals(
      "[\n  a => Test\n  b => Test\n]", 
      \xp::stringOf(array('a' => $test, 'b' => $test))
    );
  }
  
  #[@test]
  public function twice_the_same_object_with_huge_hashcode_inside_array_not_recursion() {
    $test= newinstance('lang.Object', [], array(
      'hashCode' => function($self) { return 9E100; },
      'toString' => function($self) { return 'Test'; }
    ));
    $this->assertEquals(
      "[\n  a => Test\n  b => Test\n]", 
      \xp::stringOf(array('a' => $test, 'b' => $test))
    );
  }

  #[@test]
  public function toString_calling_xp_stringOf_does_not_loop_forever() {
    $test= newinstance('lang.Object', [], array(
      'toString' => function($self) { return \xp::stringOf($self); }
    ));
    $this->assertEquals(
      $test->getClassName()." {\n  __id => \"".$test->hashCode()."\"\n}",
      \xp::stringOf($test)
    );
  }
  
  #[@test]
  public function repeated_calls_to_xp_stringOf_yield_same_result() {
    $object= new \lang\Object();
    $stringRep= $object->toString();
    $this->assertEquals(
      array($stringRep, $stringRep),
      array(\xp::stringOf($object), \xp::stringOf($object))
    );
  }

  #[@test]
  public function indenting() {
    $cl= \lang\ClassLoader::defineClass('net.xp_framework.unittest.core.StringOfTest_IndentingFixture', 'lang.Object', [], '{
      protected $inner= NULL;
      public function __construct($inner) {
        $this->inner= $inner;
      }
      public function toString() {
        return "object {\n  ".xp::stringOf($this->inner, "  ")."\n}";
      }
    }');
    $this->assertEquals(
      "object {\n  object {\n    null\n  }\n}",
      $cl->newInstance($cl->newInstance(NULL))->toString()
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
    $instance= newinstance('lang.Object', array(function($a, $b) { }), array(
      'closure'     => null,
      '__construct' => function($self, $closure) { $self->closure= $closure; },
    ));
    \xp::stringOf($instance);
  }

  #[@test]
  public function closure_inside_array_does_not_raise_serialization_exception() {
    \xp::stringOf(array(function($a, $b) { }));
  }
}
