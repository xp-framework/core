<?php namespace lang;

use ReflectionProperty, ReflectionType, ReflectionClass, ReturnTypeWillChange;

/**
 * Virtual properties
 *
 * @test net.xp_framework.unittest.reflection.VirtualMembersTest
 * @see  https://github.com/xp-framework/rfc/issues/340
 * @see  https://www.php.net/language.oop5.overloading#object.get
 * @see  https://www.php.net/language.oop5.overloading#object.set
 * @see  https://docs.phpdoc.org/latest/guide/references/phpdoc/tags/property.html
 */
class VirtualProperty extends ReflectionProperty {
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

  /** Gets name */
  public function getName(): string { return $this->_name; }

  /** Gets modifiers */
  public function getModifiers(): int { return $this->_meta[0]; }

  /** Gets doc comment */
  public function getDocComment() { return false; }

  /** Gets type */
  public function getType(): ReflectionType {
    return new class($this->_meta[1]) extends ReflectionType {
      private $_name;
      public function __construct($name) { $this->_name= $name; }
      public function getName(): string { return $this->_name; }
      public function allowsNull(): bool { return false; }
      public function __toString() { return $this->_name; }
    };
  }

  /** Gets declaring class */
  public function getDeclaringClass(): ReflectionClass { return $this->_class; }

  /**
   * Sets accessible flag
   *
   * @param  bool $flag
   * @return void
   */
  #[ReturnTypeWillChange]
  public function setAccessible($flag) { /* NOOP */ }

  /**
   * Gets this property's value
   * 
   * @param  ?object $instance
   * @return var
   */
  #[ReturnTypeWillChange]
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
  #[ReturnTypeWillChange]
  public function setValue($instance= null, $value= null) {
    $instance->__set($this->_name, $value);
  }
}