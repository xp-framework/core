<?php namespace net\xp_framework\unittest\core;

use lang\XPClass;
use util\collections\Vector;
use util\collections\HashTable;

/**
 * TestCase for create() core functionality, which is used to create
 * generic instances.
 *
 * ```php
 * $v= create('new util.collections.Vector<lang.Object>');
 * ```
 * 
 * @see   http://news.xp-framework.net/article/184/2007/05/06/
 */
class CreateTest extends \unittest\TestCase {

  #[@test]
  public function create_with_all_qualified_names() {
    $h= create('new util.collections.HashTable<lang.Object, lang.Object>');
    $this->assertEquals(
      [XPClass::forName('lang.Object'), XPClass::forName('lang.Object')], 
      $h->getClass()->genericArguments()
    );
  }

  #[@test]
  public function create_can_be_used_with_type_variables() {
    $T= XPClass::forName('lang.Object');
    $this->assertEquals([$T], create("new util.collections.Vector<$T>")->getClass()->genericArguments());
  }

  #[@test]
  public function create_invokes_constructor() {
    $this->assertEquals(
      $this,
      create('new util.collections.Vector<lang.Object>', [$this])->get(0)
    );
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function create_raises_exception_when_non_generic_given() {
    create('new lang.Object<lang.Object>');
  }
}
