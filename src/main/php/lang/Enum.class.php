<?php namespace lang;

/**
 * Enumeration base class
 *
 * @see   http://news.xp-framework.net/article/222/2007/11/12/
 * @see   http://news.xp-framework.net/article/207/2007/07/29/
 * @test  xp://net.xp_framework.unittest.core.EnumTest
 */
abstract class Enum implements Value {
  public $name= '';
  protected $ordinal= 0;

  static function __static() {
    if (self::class === static::class) return;

    // Automatically initialize this enum's public static members
    $i= 0;
    $c= new \ReflectionClass(static::class);
    foreach ($c->getProperties(\ReflectionProperty::IS_STATIC) as $prop) {
      if ($prop->isPublic()) {
        $value= $prop->getValue(null);
        if (null !== $value) $i= $value;
        $prop->setValue(null, $c->newInstance($i++, $prop->getName()));
      }
    }
  }

  /**
   * Constructor
   *
   * @param  int $ordinal default 0
   * @param  string $name default ''
   */
  public function __construct(int $ordinal= 0, string $name= '') {
    $this->ordinal= $ordinal;
    $this->name= $name;
  }

  /**
   * Returns the enumeration member uniquely identified by its name
   *
   * @param  lang.XPClass|string $type
   * @param  string $name enumeration member
   * @return self
   * @throws lang.IllegalArgumentException in case the enum member does not exist or when the given class is not an enum
   */
  public static function valueOf($type, string $name) {
    $reflect= $type instanceof XPClass ? $type->reflect() : XPClass::forName($type)->reflect();

    if ($reflect->isSubclassOf(self::class)) {
      $prop= $reflect->getStaticPropertyValue($name, null);
      if ($prop instanceof self && $reflect->isInstance($prop)) return $prop;
    } else if ($reflect->isSubclassOf(\UnitEnum::class)) {
      $case= $reflect->getConstant($name) ? : $reflect->getStaticPropertyValue($name, null);
      if ($case instanceof \UnitEnum && $reflect->isInstance($case)) return $case;
    } else {
      throw new IllegalArgumentException('Argument class must be an enum');
    }

    throw new IllegalArgumentException('Not an enum member "'.$name.'" in '.strtr($reflect->getName(), '\\', '.'));
  }

  /**
   * Returns the enumeration members for a given class
   *
   * @param  lang.XPClass|string $type
   * @return self[]
   * @throws lang.IllegalArgumentException in case the given class is not an enum
   */
  public static function valuesOf($type) {
    $reflect= $type instanceof XPClass ? $type->reflect() : XPClass::forName($type)->reflect();

    if ($reflect->isSubclassOf(self::class)) {
      $r= [];
      foreach ($reflect->getStaticProperties() as $prop) {
        $prop instanceof self && $reflect->isInstance($prop) && $r[]= $prop;
      }
      return $r;
    } else if ($reflect->isSubclassOf(\UnitEnum::class)) {
      return $reflect->getMethod('cases')->invoke(null);
    }

    throw new IllegalArgumentException('Argument class must be an enum');
  }

  /**
   * Returns all members for the called enum class
   *
   * @return self[]
   */
  public static function values() {
    $r= [];
    $c= new \ReflectionClass(static::class);
    foreach ($c->getStaticProperties() as $prop) {
      if ($prop instanceof self && $c->isInstance($prop)) {
        $r[]= $prop;
      }
    }
    return $r;
  }

  /**
   * Clone interceptor - ensures enums cannot be cloned
   *
   * @throws  lang.CloneNotSupportedException
   */
  public final function __clone() {
    throw new CloneNotSupportedException('Enums cannot be cloned');
  }

  /**
   * Returns the name of this enum constant, exactly as declared in its 
   * enum declaration.
   */
  public function name(): string { return $this->name; }
  
  /**
   * Returns the ordinal of this enumeration constant (its position in 
   * its enum declaration, where the initial constant is assigned an 
   * ordinal of zero).
   */
  public function ordinal(): int { return $this->ordinal; }

  /** Create a string representation of this enum */
  public function toString(): string { return $this->name; }

  /** Create a hashcode for this enum */
  public function hashCode(): string { return get_class($this).':'.$this->name; }

  /** Compare this enum to another value */
  public function compareTo($value): int { return $value instanceof self ? $this->name <=> $value->name : 1; }
}
