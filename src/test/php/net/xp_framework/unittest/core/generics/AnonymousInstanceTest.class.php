<?php namespace net\xp_framework\unittest\core\generics;

use lang\Primitive;
use unittest\{Assert, Test};

class AnonymousInstanceTest {

  #[Test]
  public function anonymous_generic_is_generic() {
    $filter= newinstance('net.xp_framework.unittest.core.generics.Nullable<string>', [], []);
    Assert::true(typeof($filter)->isGeneric());
  }

  #[Test]
  public function anonymous_generics_arguments() {
    $filter= newinstance('net.xp_framework.unittest.core.generics.Nullable<string>', [], []);
    Assert::equals([Primitive::$STRING], typeof($filter)->genericArguments());
  }

  #[Test]
  public function anonymous_generic_with_annotations() {
    $filter= newinstance('#[Anon] net.xp_framework.unittest.core.generics.Nullable<string>', [], []);
    Assert::true(typeof($filter)->hasAnnotation('anon'));
  }

  #[Test]
  public function class_name_contains_argument() {
    $name= nameof(newinstance('net.xp_framework.unittest.core.generics.Nullable<lang.Value>', []));
    Assert::equals("net.xp_framework.unittest.core.generics.Nullable\xb7\xb7lang\xa6Value", substr($name, 0, strrpos($name, "\xb7")), $name);
  }

  #[Test]
  public function class_name_of_generic_package_class() {
    $instance= newinstance('net.xp_framework.unittest.core.generics.ArrayFilter<lang.Value>', [], '{
      protected function accept($e) { return true; }
    }');
    $n= nameof($instance);
    Assert::equals(
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
    Assert::equals(
      [typeof($this)->getMethod('invocation')],
      $methods->filter(typeof($this)->getMethods())
    );
  }
}