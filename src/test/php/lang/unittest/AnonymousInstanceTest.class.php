<?php namespace lang\unittest;

use lang\Primitive;
use unittest\{Assert, Test};

class AnonymousInstanceTest {

  #[Test]
  public function anonymous_generic_is_generic() {
    $filter= newinstance('lang.unittest.Nullable<string>', [], []);
    Assert::true(typeof($filter)->isGeneric());
  }

  #[Test]
  public function anonymous_generics_arguments() {
    $filter= newinstance('lang.unittest.Nullable<string>', [], []);
    Assert::equals([Primitive::$STRING], typeof($filter)->genericArguments());
  }

  #[Test]
  public function anonymous_generic_with_annotations() {
    $filter= newinstance('#[Anon] lang.unittest.Nullable<string>', [], []);
    Assert::true(typeof($filter)->hasAnnotation('anon'));
  }

  #[Test]
  public function class_name_contains_argument() {
    $name= nameof(newinstance('lang.unittest.Nullable<lang.Value>', []));
    Assert::equals("lang.unittest.Nullable\xb7\xb7lang\xa6Value", substr($name, 0, strrpos($name, "\xb7")), $name);
  }

  #[Test]
  public function class_name_of_generic_package_class() {
    $instance= newinstance('lang.unittest.ArrayFilter<lang.Value>', [], '{
      protected function accept($e) { return true; }
    }');
    $n= nameof($instance);
    Assert::equals(
      "lang.unittest.ArrayFilter\xb7\xb7lang\xa6Value",
      substr($n, 0, strrpos($n, "\xb7")),
      $n
    );
  }

  #[Test]
  public function invocation() {
    $methods= newinstance('lang.unittest.ArrayFilter<lang.reflect.Method>', [], [
      'accept' => function($method) { return 'invocation' === $method->getName(); }
    ]);
    Assert::equals(
      [typeof($this)->getMethod('invocation')],
      $methods->filter(typeof($this)->getMethods())
    );
  }
}