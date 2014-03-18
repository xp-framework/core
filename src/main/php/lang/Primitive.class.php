<?php namespace lang;

/**
 * Represents primitive types:
 * 
 * - string
 * - int
 * - double
 * - bool
 *
 * @test  xp://net.xp_framework.unittest.reflection.PrimitiveTest 
 * @see   xp://lang.Type
 */
class Primitive extends Type {
  public static
    $STRING  = null,
    $INT     = null,
    $DOUBLE  = null,
    $BOOL    = null,
    $BOOLEAN = null,    // deprecated
    $ARRAY   = null,    // deprecated
    $INTEGER = null;    // deprecated
  
  static function __static() {
    self::$STRING= new self('string');
    self::$INTEGER= self::$INT= new self('int');
    self::$DOUBLE= new self('double');
    self::$BOOLEAN= self::$BOOL= new self('bool');
    self::$ARRAY= new self('array');
  }
  
  /**
   * Returns the wrapper class for this primitive
   *
   * @see     http://en.wikipedia.org/wiki/Wrapper_class
   * @return  lang.XPClass
   */
  public function wrapperClass() {
    switch ($this) {
      case self::$STRING: return XPClass::forName('lang.types.String');
      case self::$INT: return XPClass::forName('lang.types.Integer');
      case self::$DOUBLE: return XPClass::forName('lang.types.Double');
      case self::$BOOL: return XPClass::forName('lang.types.Boolean');
      case self::$ARRAY: return XPClass::forName('lang.types.ArrayList'); // deprecated
    }
  }
  
  /**
   * Boxes a type - that is, turns Generics into primitives
   *
   * @param   var in
   * @return  var the primitive if not already primitive
   * @throws  lang.IllegalArgumentException in case in cannot be unboxed.
   */
  public static function unboxed($in) {
    if ($in instanceof \lang\types\String) return $in->toString();
    if ($in instanceof \lang\types\Double) return $in->doubleValue();
    if ($in instanceof \lang\types\Integer) return $in->intValue();
    if ($in instanceof \lang\types\Boolean) return $in->value;
    if ($in instanceof \lang\types\ArrayList) return $in->values;   // deprecated
    if ($in instanceof Generic) {
      throw new \IllegalArgumentException('Cannot unbox '.\xp::typeOf($in));
    }
    return $in; // Already primitive
  }

  /**
   * Boxes a type - that is, turns primitives into Generics
   *
   * @param   var in
   * @return  lang.Generic the Generic if not already generic
   * @throws  lang.IllegalArgumentException in case in cannot be boxed.
   */
  public static function boxed($in) {
    if (null === $in || $in instanceof Generic) return $in;
    $t= gettype($in);
    if ('string' === $t) return new \lang\types\String($in);
    if ('integer' === $t) return new \lang\types\Integer($in);
    if ('double' === $t) return new \lang\types\Double($in);
    if ('boolean' === $t) return new \lang\types\Boolean($in);
    if ('array' === $t) return \lang\types\ArrayList::newInstance($in);   // deprecated
    throw new \IllegalArgumentException('Cannot box '.\xp::typeOf($in));
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
      case 'double': return self::$DOUBLE;
      case 'bool': return self::$BOOL;
      case 'array': return self::$ARRAY;    // deprecated
      case 'integer': return self::$INT;    // deprecated
      default: throw new \IllegalArgumentException('Not a primitive: '.$name);
    }
  }

  /**
   * Returns type literal
   *
   * @return  string
   */
  public function literal() {
    return 'þ'.$this->name;
  }

  /**
   * Determines whether the specified object is an instance of this
   * type. 
   *
   * @param   var obj
   * @return  bool
   */
  public function isInstance($obj) {
    return $obj === null || $obj instanceof \Generic 
      ? false 
      : $this === Type::forName(gettype($obj))
    ;
  }

  /**
   * Tests whether this type is assignable from another type
   *
   * @param   var type
   * @return  bool
   */
  public function isAssignableFrom($type) {
    return $this === ($type instanceof Type ? $type : Type::forName($type));
  }
}
