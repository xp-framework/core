<?php namespace lang;

/**
 * Type is the base class for the XPClass and Primitive classes.
 *
 * @see    xp://lang.XPClass
 * @see    xp://lang.Primitive
 * @test   xp://net.xp_framework.unittest.core.TypeResolveTest
 * @test   xp://net.xp_framework.unittest.reflection.TypeTest 
 */
class Type implements Value {
  public static $VAR, $VOID, $ARRAY, $OBJECT, $CALLABLE, $ITERABLE;
  private static $named= [];
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
      public function isInstance($value): bool { return is_object($value); }
      public function newInstance(... $args) {
        if ($args && is_object($args[0])) return clone $args[0];
        throw new IllegalAccessException("Cannot instantiate an object from ".($args ? typeof($args[0])->getName() : "null"));
      }
      public function cast($value) {
        if (null === $value || is_object($value)) return $value;
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

    self::$named= [
      'string'    => Primitive::$STRING,
      'int'       => Primitive::$INT,
      'integer'   => Primitive::$INT,
      'double'    => Primitive::$FLOAT,
      'float'     => Primitive::$FLOAT,
      'bool'      => Primitive::$BOOL,
      'boolean'   => Primitive::$BOOL,
      'false'     => Primitive::$BOOL,
      'var'       => self::$VAR,
      'resource'  => self::$VAR,
      'mixed'     => self::$VAR,
      'void'      => self::$VOID,
      'array'     => self::$ARRAY,
      'object'    => self::$OBJECT,
      'callable'  => self::$CALLABLE,
      'iterable'  => self::$ITERABLE
    ];
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
   * @param  [:(function(string): self)] $context
   * @return lang.Type[] list
   */
  public static function forNames($names, $context= []) {
    $r= [];
    foreach (self::split($names, ',') as $name) {
      $r[]= self::resolve($name, $context);
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
      if ($chars[0] === $string[$o]) $b++; else if ($chars[1] === $string[$o]) $b--;
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
      if ('(' === $string[$i]) {
        yield self::matching($string, '()', $i);
        $i= 0;
        $l= strlen($string);
      } else {
        $s= strcspn($string, $char.'<>', $i);
        $t= trim(substr($string, $i, $s));
        $n= $i + $s;
        if ($n < $l && '<' === $string[$n]) {
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
   * - Primitive types (string, int, float, bool, resource)
   * - Array and map notations (array, string[], string*, [:string])
   * - Any type (var)
   * - Void type (void)
   * - Function types
   * - Generic notations (util.collections.HashTable<string, lang.Value>)
   * - Anything else will be passed to XPClass::forName()
   *
   * @param  string $type
   * @return self
   * @throws lang.IllegalStateException if type is empty
   */
  public static function forName($type) {
    return self::resolve($type, []);
  }

  /**
   * Gets a type for a given reflection instance
   *
   * @param  php.ReflectionType $r
   * @param  ?function(): ?string $api
   * @param  [:(function(string): self)] $context
   * @return ?self
   */
  public static function forReflect($r, $api= null, $context= []) {
    if (null === $r) {

      // Check for type in api documentation
      return $api && ($s= $api()) ? self::resolve($s, $context) : null;
    } else if ($r instanceof \ReflectionUnionType) {
      $union= [];
      foreach ($r->getTypes() as $c) {
        if ('null' !== ($name= $c->getName())) {
          $union[]= self::resolve($name, $context);
        }
      }
      $t= new TypeUnion($union);
    } else {
      $name= PHP_VERSION_ID >= 70100 ? $r->getName() : $r->__toString();

      // Check array, self and callable for more specific types, e.g. `string[]`,
      // `static` or `function(): string` in api documentation
      if ($api && ('array' === $name || 'callable' === $name || 'self' === $name) && ($s= $api())) {
        return self::resolve($s, $context);
      }
      $t= self::resolve($name, $context);
    }
    return $r->allowsNull() ? new Nullable($t) : $t;
  }

  /**
   * Resolves type name
   *
   * @param  string $type
   * @param  [:(function(string): self)] $context
   * @return self
   * @throws lang.IllegalStateException if type is empty
   */
  public static function resolve($type, $context= []) {
    if (0 === ($l= strlen($type))) {
      throw new IllegalStateException('Empty type');
    }
    
    // Map well-known named types - see static constructor for list
    if ('?' === $type[0]) {
      return new Nullable(self::resolve(substr($type, 1), $context));
    } else if (isset(self::$named[$type])) {
      return self::$named[$type];
    }

    // * function(T): R is a function
    // * [:T] is a map 
    // * T<K, V> is a generic type definition D with K and V components
    //   except if any of K, V contains a ?, in which case it's a wild 
    //   card type.
    // * Anything else is a qualified or unqualified class name
    $p= strcspn($type, '<|[*(');
    if ($p === $l) {
      return isset($context[$type]) ? $context[$type]() : ((isset($context['*']) && strcspn($type, '.\\') === $l)
        ? $context['*']($type)
        : XPClass::forName($type)
      );
    } else if ('(' === $type[0]) {
      $t= self::resolve(self::matching($type, '()', 0), $context);
    } else if (0 === substr_compare($type, '[:', 0, 2)) {
      $t= new MapType(self::resolve(self::matching($type, '[]', 1), $context));
    } else if (0 === substr_compare($type, 'function(', 0, 9)) {
      $signature= self::matching($type, '()', 8);
      if ('?' === $signature) {
        $args= null;
      } else if ('' === $signature) {
        $args= [];
      } else {
        $args= self::forNames($signature, $context);
      }
      if (false === ($p= strpos($type, ':'))) {
        $return= null;
      } else {
        $return= self::resolve(trim(substr($type, $p + 1)), $context);
        $type= '';
      }
      $t= new FunctionType($args, $return);
    } else if ('<' === $type[$p]) {
      $base= substr($type, 0, $p);
      $components= [];
      $wildcard= false;
      foreach (self::split(self::matching($type, '<>', $p), ',') as $arg) {
        if ('?' === $arg) {
          $components[]= Wildcard::$ANY;
          $wildcard= true;
        } else {
          $t= self::resolve($arg, $context);
          $wildcard= $t instanceof WildcardType;
          $components[]= $t;
        }
      }
      if ($wildcard) {
        $t= new WildcardType(self::resolve($base, $context), $components);
      } else if ('array' === $base) {
        $t= 1 === sizeof($components) ? new ArrayType($components[0]) : new MapType($components[1]);
      } else {
        $t= self::resolve($base, $context)->newGenericType($components);
      }
    } else {
      $t= self::resolve(trim(substr($type, 0, $p)), $context);
      $type= substr($type, $p);
    }

    // Suffixes and unions `T[]` is an array, `T*` is a vararg, `A|B|C` is a union
    while ($type) {
      if ('*' === $type[0]) {
        $t= new ArrayType($t);
        $type= trim(substr($type, 1));
      } else if ('|' === $type[0]) {
        $components= [$t];
        foreach (self::split(substr($type, 1), '|') as $arg) {
          $components[]= self::resolve($arg, $context);
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
