<?php namespace net\xp_framework\unittest\core;

use lang\XPClass;
use lang\Object;
use util\collections\Vector;
use util\collections\HashTable;

/**
 * TestCase for create() core functionality. It has the following two purposes:
 *
 * 1) Create generics
 *
 * ```php
 * $v= create('new util.collections.Vector<lang.Object>');
 * ```
 *
 * 2) For BC with PHP 5.3 - PHP 5.4 has added constructor dereferencing! Returning
 * an object passed in, for use in fluent interfaces, e.g.
 *
 * ```php
 * $c= create(new Criteria())->add('bz_id', 20000, EQUAL);
 * ````
 * 
 * @see   http://news.xp-framework.net/article/184/2007/05/06/
 */
class CreateTest extends \unittest\TestCase {

  #[@test]
  public function create_returns_given_object_for_BC_reasons() {
    $fixture= new Object();
    $this->assertEquals($fixture, create($fixture));
  }

  #[@test]
  public function create_with_all_short_names_for_components() {
    $h= create('new util.collections.HashTable<Object, Object>');
    $this->assertEquals(
      [XPClass::forName('lang.Object'), XPClass::forName('lang.Object')], 
      $h->getClass()->genericArguments()
    );
  }

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
    $fixture= new Object();
    $this->assertEquals(
      $fixture,
      create('new util.collections.Vector<lang.Object>', [$fixture])->get(0)
    );
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function create_raises_exception_when_non_generic_given() {
    create('new lang.Object<lang.Object>');
  }
}
