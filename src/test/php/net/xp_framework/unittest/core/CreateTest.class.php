<?php namespace net\xp_framework\unittest\core;

use lang\{IllegalArgumentException, XPClass};
use unittest\Assert;
use unittest\{Expect, Test};

/**
 * TestCase for create() core functionality, which is used to create
 * generic instances.
 * 
 * @see   http://news.xp-framework.net/article/184/2007/05/06/
 */
class CreateTest {

  #[Test]
  public function create_with_all_qualified_names() {
    $h= create('new net.xp_framework.unittest.core.generics.Lookup<net.xp_framework.unittest.Name, net.xp_framework.unittest.Name>');
    Assert::equals(
      [XPClass::forName('net.xp_framework.unittest.Name'), XPClass::forName('net.xp_framework.unittest.Name')], 
      typeof($h)->genericArguments()
    );
  }

  #[Test]
  public function create_can_be_used_with_type_variables() {
    $T= XPClass::forName('net.xp_framework.unittest.Name');
    Assert::equals([$T], typeof(create("new net.xp_framework.unittest.core.generics.ListOf<$T>"))->genericArguments());
  }

  #[Test]
  public function create_invokes_constructor() {
    Assert::equals(
      $this,
      create('new net.xp_framework.unittest.core.generics.ListOf<net.xp_framework.unittest.core.CreateTest>', $this)->elements()[0]
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function create_raises_exception_when_non_generic_given() {
    create('new net.xp_framework.unittest.Name<string>');
  }
}