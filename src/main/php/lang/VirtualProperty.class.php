<?php namespace lang;

use ReflectionProperty, ReflectionType, ReflectionClass, ReturnTypeWillChange;

/**
 * Virtual properties
 *
 * Note the "name" and "class" members are not available as they cannot
 * be assigned in inherited classes.
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

  /** Checks if property has a type */
  public function hasType(): bool { return isset($this->_meta[1]); }

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

  /** Gets doc comment */
  #[ReturnTypeWillChange]
  public function getDocComment() { return false; }

  /** Gets whether a default value is available */
  public function hasDefaultValue(): bool { return false; }

  /** Gets default value */
  #[ReturnTypeWillChange]
  public function getDefaultValue() { return null; }

  /** Checks if property is a default property */
  public function isDefault(): bool { return false; }

  /** Returns whether this property is public */
  public function isPublic(): bool { return MODIFIER_PUBLIC === ($this->_meta[0] & MODIFIER_PUBLIC); }

  /** Returns whether this property is protected */
  public function isProtected(): bool { return MODIFIER_PROTECTED === ($this->_meta[0] & MODIFIER_PROTECTED); }

  /** Returns whether this property is private */
  public function isPrivate(): bool { return MODIFIER_PRIVATE === ($this->_meta[0] & MODIFIER_PRIVATE); }

  /** Returns whether this property is private */
  public function isStatic(): bool { return MODIFIER_STATIC === ($this->_meta[0] & MODIFIER_STATIC); }

  /**
   * Returns all attributes declared on this class property as an array of ReflectionAttribute.
   *
   * @param  ?string $name
   * @param  int $flags
   * @return \ReflectionAttribute[]
   */
  public function getAttributes(string $name= null, int $flags= 0): array {
    return [];
  }

  /**
   * Sets accessible flag
   *
   * @param  bool $flag
   * @return void
   */
  #[ReturnTypeWillChange]
  public function setAccessible($flag) { /* NOOP */ }

  /**
   * Checks whether a property is initialized.
   *
   * @param  ?object $object
   * @return bool
   */
  public function isInitialized($object= null): bool {
    return null !== $instance->__get($this->_name);
  }

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

  /**
   * Compares to property instances
   *
   * @param  \ReflectionProperty $a
   * @param  \ReflectionProperty $b
   * @return int
   */
  public static function compare($a, $b) {
    if ($a instanceof self && $b instanceof self) {
      $r= $a->_class <=> $b->_class;
      return 0 === $r ? $a->_name <=> $b->_name : $r;
    } else if ($a instanceof self) {
      $r= $a->_class <=> $b->class;
      return 0 === $r ? $a->_name <=> $b->name : $r;
    } else if ($b instanceof self) {
      $r= $a->class <=> $b->_class;
      return 0 === $r ? $a->name <=> $b->_name : $r;
    } else {
      $r= $a->class <=> $b->class;
      return 0 === $r ? $a->name <=> $b->name : $r;
    }
  }
}