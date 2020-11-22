<?php namespace lang;

/**
 * Represents nullable types
 *
 * @see   xp://lang.Type
 * @test  xp://net.xp_framework.unittest.core.NullableTypeTest
 */
class NullableType extends Type {
  private $underlying;

  static function __static() { }

  /**
   * Creates a new nullable type instance
   *
   * @param string|lang.Type $underlying
   */
  public function __construct($underlying) {
    if ($underlying instanceof Type) {
      $this->underlying= $underlying;
      parent::__construct('?'.$underlying->getName(), []);
    } else {
      $this->underlying= Type::forName($underlying);
      parent::__construct('?'.$underlying, []);
    }
  }

  /** Gets the underlying type */
  public function underlyingType(): Type { return $this->underlying; }

  /** Returns type literal */
  public function literal(): string { return "\xac".$this->underlying->literal(); }

  /**
   * Get a type instance for a given name
   *
   * @param  string $name
   * @return self
   * @throws lang.IllegalArgumentException if the given name does not correspond to a primitive
   */
  public static function forName($name) {
    if ('?' !== $name[0]) {
      throw new IllegalArgumentException('Not nullable: '.$name);
    }
    
    return new self(substr($name, 1));
  }

  /**
   * Determines whether the specified object is an instance of this
   * type. 
   *
   * @param  var $obj
   * @return bool
   */
  public function isInstance($obj): bool {
    return null === $obj || $this->underlying->isInstance($obj);
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var... $args
   * @return  var
   */
  public function newInstance(... $args) {
    if (empty($args) || null === $args[0]) {
      return null;
    } else {
      return $this->underlying->newInstance(...$args);
    }
  }

  /**
   * Cast a value to this type
   *
   * @param   var value
   * @return  var
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    if (null === $value) {
      return null;
    } else {
      return $this->underlying->cast($value);
    }
  }

  /** Tests whether this type is assignable from another type */
  public function isAssignableFrom($type): bool {
    $t= $type instanceof Type ? $type : Type::forName($type);
    return $t instanceof self && $t->underlying->isAssignableFrom($this->underlying);
  }
}
