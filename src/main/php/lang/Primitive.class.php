<?php namespace lang;

/**
 * Represents primitive types:
 * 
 * - string
 * - int
 * - float
 * - bool
 *
 * @test  xp://net.xp_framework.unittest.reflection.PrimitiveTest 
 * @see   xp://lang.Type
 */
class Primitive extends Type {
  public static
    $STRING  = null,
    $INT     = null,
    $FLOAT   = null,
    $BOOL    = null;
  
  static function __static() {
    self::$STRING= new self('string', '');
    self::$INT= new self('int', 0);
    self::$FLOAT= new self('float', 0.0);
    self::$BOOL= new self('bool', false);
  }
  
  /**
   * Get a type instance for a given name
   *
   * @param   string name
   * @return  lang.Type
   * @throws  lang.IllegalArgumentException if the given name does not correspond to a primitive
   */
  public static function forName($name) {
    switch ($name) {
      case 'string': return self::$STRING;
      case 'int': return self::$INT;
      case 'float': case 'double': return self::$FLOAT;
      case 'bool': return self::$BOOL;
      default: throw new IllegalArgumentException('Not a primitive: '.$name);
    }
  }

  /** Returns type literal */
  public function literal(): string {
    return '�'.$this->name;
  }

  /** Determines whether the specified object is an instance of this type. */
  public function isInstance($obj): bool {
    return is_scalar($obj) && $this === Type::forName(gettype($obj));
  }

  /**
   * Helper for cast() and newInstance()
   *
   * @param  var $value
   * @param  var $default A function
   * @return var
   */
  protected function coerce($value, $default) {
    if (!is_array($value)) switch ($this) {
      case self::$STRING: return (string)$value;
      case self::$FLOAT: return (float)$value;
      case self::$BOOL: return (bool)$value;
      case self::$INT:
        if (strlen($value) <= 1) {
          return (int)$value;
        } else if ('x' === $value{1}) {
          return hexdec($value);
        } else if ('0' === $value{0}) {
          return octdec($value);
        } else {
          return (int)$value;
        }
    }

    return $default($value);
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var... $args
   * @return  var
   */
  public function newInstance(...$args) {
    return $this->coerce($args[0] ?? null, function($value) {
      throw new IllegalArgumentException('Cannot create instances of '.$this->getName().' from '.typeof($value)->getName());
    });
  }

  /**
   * Cast a value to this type
   *
   * @param   var value
   * @return  var
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    return null === $value ? null : $this->coerce($value, function($value) {
      throw new ClassCastException('Cannot cast to '.$this->getName().' from '.typeof($value)->getName());
    });
  }

  /** Tests whether this type is assignable from another type */
  public function isAssignableFrom($type): bool {
    return $this === ($type instanceof Type ? $type : Type::forName($type));
  }
}
