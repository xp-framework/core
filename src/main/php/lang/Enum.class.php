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
    $class= $type instanceof XPClass ? $type : XPClass::forName($type);

    try {
      if ($class->isSubclassOf(self::class)) {
        $prop= $class->reflect()->getStaticPropertyValue($name);
        if ($class->isInstance($prop)) return $prop;
        throw new IllegalArgumentException('No such member "'.$name.'" in '.$class->getName());
      } else if ($class->isSubclassOf(\UnitEnum::class)) {
        return $class->reflect()->getConstant($name);
      }
    } catch (\ReflectionException $e) {
      throw new IllegalArgumentException($e->getMessage());
    }

    throw new IllegalArgumentException('Argument class must be an enum');
  }

  /**
   * Returns the enumeration members for a given class
   *
   * @param  lang.XPClass|string $type
   * @return self[]
   * @throws lang.IllegalArgumentException in case the given class is not an enum
   */
  public static function valuesOf($type) {
    $class= $type instanceof XPClass ? $type : XPClass::forName($type);

    if ($class->isSubclassOf(self::class)) {
      $r= [];
      foreach ($class->reflect()->getStaticProperties() as $prop) {
        $class->isInstance($prop) && $r[]= $prop;
      }
      return $r;
    } else if ($class->isSubclassOf(\UnitEnum::class)) {
      return $class->getMethod('cases')->invoke(null);
    }

    throw new IllegalArgumentException('Argument class must be enum');
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
