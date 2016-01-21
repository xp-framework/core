<?php namespace net\xp_framework\unittest\core\generics;

use lang\Primitive;

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.ArrayFilter
 */
class AnonymousInstanceTest extends \unittest\TestCase {

  #[@test]
  public function anonymous_generic_is_generic() {
    $filter= newinstance('util.collections.Vector<string>', [], []);
    $this->assertTrue($filter->getClass()->isGeneric());
  }

  #[@test]
  public function anonymous_generics_arguments() {
    $filter= newinstance('util.collections.Vector<string>', [], []);
    $this->assertEquals([Primitive::$STRING], $filter->getClass()->genericArguments());
  }

  #[@test]
  public function anonymous_generic_with_annotations() {
    $filter= newinstance('#[@anon] util.collections.Vector<string>', [], []);
    $this->assertTrue($filter->getClass()->hasAnnotation('anon'));
  }

  #[@test]
  public function class_name_contains_argument() {
    $name= nameof(newinstance('util.collections.Vector<lang.Object>', []));
    $this->assertEquals("util.collections.Vector\xb7\xb7lang\xa6Object", substr($name, 0, strrpos($name, "\xb7")), $name);
  }

  #[@test]
  public function class_name_of_generic_package_class() {
    $instance= newinstance('net.xp_framework.unittest.core.generics.ArrayFilter<lang.Object>', [], '{
      protected function accept($e) { return true; }
    }');
    $n= nameof($instance);
    $this->assertEquals(
      "net.xp_framework.unittest.core.generics.ArrayFilter\xb7\xb7lang\xa6Object",
      substr($n, 0, strrpos($n, "\xb7")),
      $n
    );
  }

  #[@test]
  public function invocation() {
    $methods= newinstance('net.xp_framework.unittest.core.generics.ArrayFilter<lang.reflect.Method>', [], [
      'accept' => function($method) { return 'invocation' === $method->getName(); }
    ]);
    $this->assertEquals(
      [$this->getClass()->getMethod('invocation')],
      $methods->filter($this->getClass()->getMethods())
    );
  }
}
