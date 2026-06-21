<?php namespace lang\unittest;

use lang\Primitive;
use test\verify\Runtime;
use test\{Assert, Test};
use util\Bytes;

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

  #[Test, Runtime(php: '>=8.0')]
  public function anonymous_generic_with_annotations() {
    $filter= newinstance('#[Anon] lang.unittest.Nullable<string>', [], []);
    Assert::equals('lang\\unittest\\Anon', typeof($filter)->reflect()->getAttributes()[0]->getName());
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
    $nonEmpty= newinstance('lang.unittest.ArrayFilter<util.Bytes>', [], [
      'accept' => function($bytes) { return $bytes->size() > 0; }
    ]);
    Assert::equals(
      [new Bytes('Test')],
      $nonEmpty->filter([new Bytes(''), new Bytes('Test')])
    );
  }
}