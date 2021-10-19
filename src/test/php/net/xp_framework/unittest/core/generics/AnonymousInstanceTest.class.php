<?php namespace net\xp_framework\unittest\core\generics;

use lang\Primitive;
use unittest\Test;

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.ArrayFilter
 */
class AnonymousInstanceTest extends \unittest\TestCase {

  #[Test]
  public function anonymous_generic_is_generic() {
    $filter= newinstance('net.xp_framework.unittest.core.generics.Nullable<string>', [], []);
    $this->assertTrue(typeof($filter)->isGeneric());
  }

  #[Test]
  public function anonymous_generics_arguments() {
    $filter= newinstance('net.xp_framework.unittest.core.generics.Nullable<string>', [], []);
    $this->assertEquals([Primitive::$STRING], typeof($filter)->genericArguments());
  }

  #[Test]
  public function anonymous_generic_with_annotations() {
    $filter= newinstance('#[Anon] net.xp_framework.unittest.core.generics.Nullable<string>', [], []);
    $this->assertTrue(typeof($filter)->hasAnnotation('anon'));
  }

  #[Test]
  public function class_name_contains_argument() {
    $name= nameof(newinstance('net.xp_framework.unittest.core.generics.Nullable<lang.Value>', []));
    $this->assertEquals("net.xp_framework.unittest.core.generics.Nullable\xb7\xb7lang\xa6Value", substr($name, 0, strrpos($name, "\xb7")), $name);
  }

  #[Test]
  public function class_name_of_generic_package_class() {
    $instance= newinstance('net.xp_framework.unittest.core.generics.ArrayFilter<lang.Value>', [], '{
      protected function accept($e) { return true; }
    }');
    $n= nameof($instance);
    $this->assertEquals(
      "net.xp_framework.unittest.core.generics.ArrayFilter\xb7\xb7lang\xa6Value",
      substr($n, 0, strrpos($n, "\xb7")),
      $n
    );
  }

  #[Test]
  public function invocation() {
    $methods= newinstance('net.xp_framework.unittest.core.generics.ArrayFilter<lang.reflect.Method>', [], [
      'accept' => function($method) { return 'invocation' === $method->getName(); }
    ]);
    $this->assertEquals(
      [typeof($this)->getMethod('invocation')],
      $methods->filter(typeof($this)->getMethods())
    );
  }
}