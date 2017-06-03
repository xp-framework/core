<?php namespace lang;

/**
 * Type is the base class for the XPClass and Primitive classes.
 *
 * @see    xp://lang.XPClass
 * @see    xp://lang.Primitive
 * @test   xp://net.xp_framework.unittest.reflection.TypeTest 
 */
class Type implements Value {
  public static $VAR, $VOID, $ARRAY, $OBJECT, $CALLABLE, $ITERABLE;
  public $name;
  public $default;

  static function __static() {
    self::$VAR= new self('var', null);
    self::$VOID= new self('void', null);

    self::$ARRAY= eval('namespace lang; class NativeArrayType extends Type {
      static function __static() { }
      public function isInstance($value): bool { return is_array($value); }
      public function newInstance($value= null) {
        return null === $value ? [] : (array)$value;
      }
      public function cast($value) {
        return null === $value ? null : (array)$value;
      }
      public function isAssignableFrom($type): bool {
        return $type instanceof self || $type instanceof ArrayType || $type instanceof MapType;
      }
    } return new NativeArrayType("array", []);');

    self::$OBJECT= eval('namespace lang; class NativeObjectType extends Type {
      static function __static() { }
      public function isInstance($value): bool { return is_object($value) && !$value instanceof \Closure; }
      public function newInstance($value= null) {
        if (is_object($value) && !$value instanceof \Closure) return clone $value;
        throw new IllegalAccessException("Cannot instantiate ".typeof($value)->getName());
      }
      public function cast($value) {
        if (null === $value || is_object($value) && !$value instanceof \Closure) return $value;
        throw new ClassCastException("Cannot cast ".typeof($value)->getName()." to the object type");
      }
      public function isAssignableFrom($type): bool {
        return $type instanceof self || $type instanceof XPClass;
      }
    } return new NativeObjectType("object", null);');

    self::$CALLABLE= eval('namespace lang; class NativeCallableType extends Type {
      static function __static() { }
      public function isInstance($value): bool { return is_callable($value); }
      public function newInstance($value= null) {
        if (is_callable($value)) return $value;
        throw new IllegalAccessException("Cannot instantiate callable type from ".typeof($value)->getName());
      }
      public function cast($value) {
        if (null === $value || is_callable($value)) return $value;
        throw new ClassCastException("Cannot cast ".typeof($value)->getName()." to the callable type");
      }
      public function isAssignableFrom($type): bool {
        return $type instanceof self || $type instanceof FunctionType;
      }
    } return new NativeCallableType("callable", null);');

    self::$ITERABLE= eval('namespace lang; class NativeIterableType extends Type {
      static function __static() { }
      public function isInstance($value): bool { return $value instanceof \Traversable || is_array($value); }
      public function newInstance($value= null) {
        if ($value instanceof \Traversable || is_array($value)) return $value;
        throw new IllegalAccessException("Cannot instantiate iterable type from ".typeof($value)->getName());
      }
      public function cast($value) {
        if (null === $value || $value instanceof \Traversable || is_array($value)) return $value;
        throw new ClassCastException("Cannot cast ".typeof($value)->getName()." to the iterable type");
      }
      public function isAssignableFrom($type): bool {
        return $type instanceof self;
      }
    } return new NativeIterableType("iterable", null);');
  }

  /**
   * Constructor
   *
   * @param  string $name
   * @param  var $default
   */
  protected function __construct($name, $default) {
    $this->name= $name;
    $this->default= $default;
  }

  /** Retrieves this type's name  */
  public function getName(): string { return $this->name; }
  
  /** Creates a string representation of this object */
  public function toString(): string { return nameof($this).'<'.$this->name.'>'; }

  /** Returns a hashcode for this object */
  public function hashCode(): string { return get_class($this).':'.$this->name; }

  /** Compares to another value */
  public function compareTo($value): int {
    return $value instanceof self ? $this->name <=> $value->name : 1;
  }

  /** Checks for equality with another value */
  public function equals($value): bool {
    return $value instanceof self && $this->name === $value->name;
  }

  /**
   * Creates a type list from a given string
   *
   * @param  string $names
   * @return lang.Type[] list
   */
  public static function forNames($names) {
    $types= [];
    for ($args= $names.',', $o= 0, $brackets= 0, $i= 0, $s= strlen($args); $i < $s; $i++) {
      if (',' === $args{$i} && 0 === $brackets) {
        $types[]= self::forName(ltrim(substr($args, $o, $i- $o)));
        $o= $i+ 1;
      } else if ('<' === $args{$i}) {
        $brackets++;
      } else if ('>' === $args{$i}) {
        $brackets--;
      }
    }
    return $types;
  }
  
  /**
   * Gets a type for a given name
   *
   * Checks for:
   * - Primitive types (string, int, double, boolean, resource)
   * - Array and map notations (array, string[], string*, [:string])
   * - Any type (var)
   * - Void type (void)
   * - Function types
   * - Generic notations (util.collections.HashTable<string, lang.Value>)
   * - Anything else will be passed to XPClass::forName()
   *
   * @param  string $type
   * @return lang.Type
   * @throws lang.IllegalStateException if type is empty
   */
  public static function forName($type) {
    static $primitives= [
      'string'    => 'string',
      'int'       => 'int',
      'integer'   => 'int',
      'double'    => 'double',
      'float'     => 'double',
      'bool'      => 'bool',
      'boolean'   => 'bool',
      'HH\int'    => 'int',
      'HH\string' => 'string',
      'HH\float'  => 'double',
      'HH\bool'   => 'bool'
    ];

    if (0 === strlen($type)) {
      throw new IllegalStateException('Empty type');
    }
    
    // Map well-known primitives, var and void, handle rest syntactically:
    // * T[] is an array
    // * [:T] is a map 
    // * T* is a vararg
    // * T<K, V> is a generic
    // * D<K, V> is a generic type definition D with K and V components
    //   except if any of K, V contains a ?, in which case it's a wild 
    //   card type.
    // * T1|T2 is a type union
    // * Anything else is a qualified or unqualified class name
    if (isset($primitives[$type])) {
      return Primitive::forName($primitives[$type]);
    } else if ('var' === $type || 'resource' === $type || 'HH\mixed' === $type) {
      return self::$VAR;
    } else if ('void' === $type || 'HH\void' === $type || 'HH\noreturn' === $type) {
      return self::$VOID;
    } else if ('array' === $type) {
      return self::$ARRAY;
    } else if ('object' === $type) {
      return self::$OBJECT;
    } else if ('callable' === $type) {
      return self::$CALLABLE;
    } else if ('iterable' === $type) {
      return self::$ITERABLE;
    } else if (0 === substr_compare($type, 'function(', 0, 9)) {
      return FunctionType::forName($type);
    } else if (0 === substr_compare($type, '[]', -2)) {
      return new ArrayType(self::forName(substr($type, 0, -2)));
    } else if (0 === substr_compare($type, '[:', 0, 2)) {
      return new MapType(self::forName(substr($type, 2, -1)));
    } else if ('?' === $type{0} || '@' === $type{0}) {
      return self::forName(substr($type, 1));
    } else if ('(' === $type{0}) {
      return self::forName(substr($type, 1, -1));
    } else if (0 === substr_compare($type, '*', -1)) {
      return new ArrayType(self::forName(substr($type, 0, -1)));
    } else if (strstr($type, '|')) {
      return TypeUnion::forName($type);
    } else if ('HH\num' === $type) {
      return new TypeUnion([Primitive::$INT, Primitive::$DOUBLE]);
    } else if ('HH\arraykey' === $type) {
      return new TypeUnion([Primitive::$INT, Primitive::$STRING]);
    } else if (false === ($p= strpos($type, '<'))) {
      return XPClass::forName($type);
    } else if (strstr($type, '?')) {
      return WildcardType::forName($type);
    } else {
      $base= substr($type, 0, $p);
      $components= self::forNames(substr($type, $p + 1, -1));
      if ('array' === $base) {
        return 1 === sizeof($components) ? new ArrayType($components[0]) : new MapType($components[1]);
      } else {
        return XPClass::forName($base)->newGenericType($components);
      }
    }
  }
  
  /** Returns type literal */
  public function literal(): string { return $this->name; }

  /** Determines whether the specified object is an instance of this type. */
  public function isInstance($value): bool {
    return self::$VAR === $this;      // VAR is always true, VOID never
  }

  /**
   * Returns a new instance of this object
   *
   * @param  var $value
   * @return var
   */
  public function newInstance($value= null) {
    if (self::$VAR === $this) return $value;
    throw new IllegalAccessException('Cannot instantiate '.$this->name.' type');
  }

  /**
   * Cast a value to this type
   *
   * @param  var $value
   * @return var
   * @throws lang.ClassCastException
   */
  public function cast($value) {
    if (self::$VAR === $this) return $value;
    throw new ClassCastException('Cannot cast '.typeof($value)->getName().' to the void type');
  }

  /** Tests whether this type is assignable from another type */
  public function isAssignableFrom($type): bool { return self::$VAR === $this && self::$VOID !== $type; }

  /** Creates a string representation of this object */
  public function __toString(): string { return $this->name; }
}
