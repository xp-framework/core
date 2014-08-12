<?php namespace net\xp_framework\unittest\core;

/**
 * Fixture for generic method invocation
 */
class GenericMethodFixture extends \lang\Object {

  /**
   * Generic getter
   *
   * @param  var $arg
   * @return T
   */
  #[@generic(self= 'T', return= 'T')]
  public function get($T, $arg= null) {
    return null === $arg ? $T->default : $T->cast($arg);
  }

  /**
   * Generic creator
   *
   * @param  T[] $arg
   * @return util.collections.IList<T>
   */
  #[@generic(self= 'T', return= 'util.collections.IList<T>', params= 'T[]')]
  public static function asList($T, $arg) {
    $list= \lang\XPClass::forName('util.collections.Vector')->newGenericType([$T])->newInstance();
    $list->addAll($arg);
    return $list;
  }

  /**
   * Creates a new hashtable
   *
   * @return util.collections.HashTable<K, V>
   */
  #[@generic(self= 'K, V', return= 'util.collections.HashTable<K, V>')]
  public static function newHash($K, $V) {
    return \lang\XPClass::forName('util.collections.HashTable')->newGenericType([$K, $V])->newInstance();
  }
}
