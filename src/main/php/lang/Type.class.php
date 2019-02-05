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
      public function newInstance(... $args) {
        switch (sizeof($args)) {
          case 0: return [];
          case 1: return (array)$args[0];
          default: return $args;
        }
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
      public function newInstance(... $args) {
        if ($args && is_object($args[0]) && !$args[0] instanceof \Closure) return clone $args[0];
        throw new IllegalAccessException("Cannot instantiate an object from ".($args ? typeof($args[0])->getName() : "null"));
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
      public function newInstance(... $args) {
        if ($args && is_callable($args[0])) return $args[0];
        throw new IllegalAccessException("Cannot instantiate a callable from ".($args ? typeof($args[0])->getName() : "null"));
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
      public function newInstance(... $args) {
        if ($args && $args[0] instanceof \Traversable || is_array($args[0])) return $args[0];
        throw new IllegalAccessException("Cannot instantiate an iterable from ".($args ? typeof($args[0])->getName() : "null"));
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
    return $value instanceof static ? $this->name <=> $value->name : 1;
  }

  /** Checks for equality with another value */
  public function equals($value): bool {
    return $value instanceof static && $this->name === $value->name;
  }

  /**
   * Creates a type list from a given string
   *
   * @param  string $names
   * @return lang.Type[] list
   */
  public static function forNames($names) {
    $r= [];
    foreach (self::split($names, ',') as $name) {
      $r[]= self::forName($name);
    }
    return $r;
  }

  /**
   * Returns a substring between matching braces
   *
   * @param  string $string
   * @param  string $char
   * @param  int $offset
   * @return string
   */
  private static function matching(&$string, $chars, $offset= 0) {
    for ($b= 1, $o= $offset, $s= 1, $l= strlen($string); $b && (($o+= $s) < $l); $o++, $s= strcspn($string, $chars, $o)) {
      if ($chars{0} === $string{$o}) $b++; else if ($chars{1} === $string{$o}) $b--;
    }

    if (0 === $b) {
      $type= substr($string, $offset + 1, $o - $offset - 2);
      $string= substr($string, $o);
      return $type;
    }

    throw new IllegalArgumentException('Unmatched '.$chars);
  }

  /**
   * Splits a string including handling of braces
   *
   * @param  string $string
   * @param  string $char
   * @return iterable
   */
  private static function split($string, $char) {
    for ($i= 0, $l= strlen($string); $i < $l; $i++) {
      if ('(' === $string{$i}) {
        yield self::matching($string, '()', $i);
        $i= 0;
        $l= strlen($string);
      } else {
        $s= strcspn($string, $char.'<>', $i);
        $t= trim(substr($string, $i, $s));
        $n= $i + $s;
        if ($n < $l && '<' === $string{$n}) {
          yield $t.'<'.self::matching($string, '<>', $n).'>';
          $i= 0;
          $l= strlen($string);
        } else {
          yield trim(substr($string, $i, $s));
          $i+= $s;
        }
      }
    }
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
      'double'    => 'float',
      'float'     => 'float',
      'bool'      => 'bool',
      'boolean'   => 'bool',
    ];

    $l= strlen($type);
    if (0 === $l) {
      throw new IllegalStateException('Empty type');
    }
    
    // Map well-known primitives, var, void, union types as well as nullable and soft types
    if (isset($primitives[$type])) {
      return Primitive::forName($primitives[$type]);
    } else if ('var' === $type || 'resource' === $type) {
      return self::$VAR;
    } else if ('void' === $type) {
      return self::$VOID;
    } else if ('array' === $type) {
      return self::$ARRAY;
    } else if ('object' === $type) {
      return self::$OBJECT;
    } else if ('callable' === $type) {
      return self::$CALLABLE;
    } else if ('iterable' === $type) {
      return self::$ITERABLE;
    } else if ('HH\num' === $type) {
      return new TypeUnion([Primitive::$INT, Primitive::$FLOAT]);
    } else if ('HH\arraykey' === $type) {
      return new TypeUnion([Primitive::$INT, Primitive::$STRING]);
    } else if ('?' === $type{0} || '@' === $type{0}) {
      return self::forName(substr($type, 1));
    }

    // * function(T): R is a function
    // * [:T] is a map 
    // * T<K, V> is a generic type definition D with K and V components
    //   except if any of K, V contains a ?, in which case it's a wild 
    //   card type.
    // * Anything else is a qualified or unqualified class name
    $p= strcspn($type, '<|[*(');
    if ($p >= $l) {
      return XPClass::forName($type);
    } else if ('(' === $type{0}) {
      $t= self::forName(self::matching($type, '()', 0));
    } else if (0 === substr_compare($type, '[:', 0, 2)) {
      $t= new MapType(self::forName(self::matching($type, '[]', 1)));
    } else if (0 === substr_compare($type, 'function(', 0, 2)) {
      $signature= self::matching($type, '()', 8);
      if ('?' === $signature) {
        $args= null;
      } else if ('' === $signature) {
        $args= [];
      } else {
        $args= self::forNames($signature);
      }
      if (false === ($p= strpos($type, ':'))) {
        $return= null;
      } else {
        $return= self::forName(trim(substr($type, $p + 1)));
        $type= '';
      }
      $t= new FunctionType($args, $return);
    } else if ('<' === $type{$p}) {
      $base= substr($type, 0, $p);
      $components= [];
      $wildcard= false;
      foreach (self::split(self::matching($type, '<>', $p), ',') as $arg) {
        if ('?' === $arg) {
          $components[]= Wildcard::$ANY;
          $wildcard= true;
        } else {
          $t= self::forName($arg);
          $wildcard= $t instanceof WildcardType;
          $components[]= $t;
        }
      }
      if ($wildcard) {
        $t= new WildcardType(XPClass::forName($base), $components);
      } else if ('array' === $base) {
        $t= 1 === sizeof($components) ? new ArrayType($components[0]) : new MapType($components[1]);
      } else {
        $t= XPClass::forName($base)->newGenericType($components);
      }
    } else {
      $t= self::forName(trim(substr($type, 0, $p)));
      $type= substr($type, $p);
    }

    // Suffixes and unions `T[]` is an array, `T*` is a vararg, `A|B|C` is a union
    while ($type) {
      if ('*' === $type{0}) {
        $t= new ArrayType($t);
        $type= trim(substr($type, 1));
      } else if ('|' === $type{0}) {
        $components= [$t];
        foreach (self::split(substr($type, 1), '|') as $arg) {
          $components[]= self::forName($arg);
        }
        return new TypeUnion($components);
      } else if (0 === substr_compare($type, '[]', 0, 2)) {
        $t= new ArrayType($t);
        $type= trim(substr($type, 2));
      } else {
        throw new IllegalArgumentException('Invalid type suffix '.$type);
      }
    }

    return $t;
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
   * @param  var... $args
   * @return var
   */
  public function newInstance(... $args) {
    if (self::$VAR === $this) return $args[0];
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
