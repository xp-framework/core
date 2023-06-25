<?php namespace lang\unittest;

use lang\{IllegalArgumentException, XPClass};
use test\{Assert, Expect, Test};

class CreateTest {

  #[Test]
  public function create_with_all_qualified_names() {
    $h= create('new lang.unittest.Lookup<lang.unittest.Name, lang.unittest.Name>');
    Assert::equals(
      [XPClass::forName('lang.unittest.Name'), XPClass::forName('lang.unittest.Name')], 
      typeof($h)->genericArguments()
    );
  }

  #[Test]
  public function create_can_be_used_with_type_variables() {
    $T= XPClass::forName('lang.unittest.Name');
    Assert::equals([$T], typeof(create("new lang.unittest.ListOf<$T>"))->genericArguments());
  }

  #[Test]
  public function create_invokes_constructor() {
    Assert::equals(
      $this,
      create('new lang.unittest.ListOf<lang.unittest.CreateTest>', $this)->elements()[0]
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function create_raises_exception_when_non_generic_given() {
    create('new lang.unittest.Name<string>');
  }
}