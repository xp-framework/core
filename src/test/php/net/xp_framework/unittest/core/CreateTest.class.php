<?php namespace net\xp_framework\unittest\core;

use lang\XPClass;
use lang\IllegalArgumentException;

/**
 * TestCase for create() core functionality, which is used to create
 * generic instances.
 * 
 * @see   http://news.xp-framework.net/article/184/2007/05/06/
 */
class CreateTest extends \unittest\TestCase {

  #[@test]
  public function create_with_all_qualified_names() {
    $h= create('new net.xp_framework.unittest.core.generics.Lookup<lang.Object, lang.Object>');
    $this->assertEquals(
      [XPClass::forName('lang.Object'), XPClass::forName('lang.Object')], 
      typeof($h)->genericArguments()
    );
  }

  #[@test]
  public function create_can_be_used_with_type_variables() {
    $T= XPClass::forName('lang.Object');
    $this->assertEquals([$T], typeof(create("new net.xp_framework.unittest.core.generics.ListOf<$T>"))->genericArguments());
  }

  #[@test]
  public function create_invokes_constructor() {
    $this->assertEquals(
      $this,
      create('new net.xp_framework.unittest.core.generics.ListOf<lang.Object>', $this)->elements()[0]
    );
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function create_raises_exception_when_non_generic_given() {
    create('new lang.Object<lang.Object>');
  }
}
