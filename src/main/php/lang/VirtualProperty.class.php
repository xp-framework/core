<?php namespace lang;

/**
 * Virtual properties
 *
 * @test net.xp_framework.unittest.reflection.VirtualMembersTest
 * @see  https://github.com/xp-framework/rfc/issues/340
 * @see  https://www.php.net/language.oop5.overloading#object.get
 * @see  https://www.php.net/language.oop5.overloading#object.set
 * @see  https://docs.phpdoc.org/latest/guide/references/phpdoc/tags/property.html
 */
class VirtualProperty extends \ReflectionProperty {
  private $_class, $_name, $_meta;

  /**
   * Creates a new virtual property
   *
   * @param \ReflectionClass $class
   * @param string $name
   * @param var[] $meta
   */
  public function __construct($class, $name, $meta= [MODIFIER_PUBLIC, null]) {
    $this->_class= $class;
    $this->_name= $name;
    $this->_meta= $meta;
  }

  /** @return string */
  public function getName() { return $this->_name; }

  /** @return int */
  public function getModifiers() { return $this->_meta[0]; }

  /** @return \ReflectionType */
  public function getType() {
    return new class($this->_meta[1]) extends \ReflectionNamedType {
      public function __construct($name) { $this->name= $name; }
      public function getName() { return $this->name; }
    };
  }

  /** @return \ReflectionClass */
  public function getDeclaringClass() { return $this->_class; }

  /**
   * Sets accessible flag
   *
   * @param  bool $flag
   * @return void
   */
  public function setAccessible($flag) { /* NOOP */ }

  /**
   * Gets this property's value
   * 
   * @param  ?object $instance
   * @return var
   */
  public function getValue($instance= null) {
    return $instance->__get($this->_name);
  }

  /**
   * Sets this property's value
   * 
   * @param  ?object $instance
   * @param  var $value
   * @return void
   */
  public function setValue($instance= null, $value= null) {
    $instance->__set($this->_name, $value);
  }
}