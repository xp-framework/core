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
   * Creates a type list from a given string
   *
   * @param  string $names
   * @param  [:(function(string): self)] $context
   * @return self[]
   */
  public static function forNames($names, $context= []) {
    $r= [];
    foreach (self::split($names, ',') as $name) {
      $r[]= self::named($name, $context);
    }
    return $r;
  }

  /**
   * Resolves type name
   *
   * @param  string $type
   * @return self
   * @throws lang.IllegalStateException if type is empty
   */
  public static function forName($type) {
    if (0 === strlen($type)) throw new IllegalStateException('Empty type');
    return self::named($type, []);
  }

  /**
   * Resolves a type
   *
   * @param  ?string|php.ReflectionType $type
   * @param  [:(function(string): self)] $context
   * @param  ?function(?bool): ?string $api
   * @return ?self
   */
  public static function resolve($type, $context= [], $api= null) {
    if (null === $type) {

      // Check for type in api documentation
      return $api && ($s= $api(false)) ? self::named($s, $context) : null;
    } else if ($type instanceof \ReflectionUnionType) {
      $union= [];
      foreach ($type->getTypes() as $c) {
        if ('null' !== ($name= $c->getName())) {
          $union[]= self::named($name, $context);
        }
      }
      $t= new TypeUnion($union);
    } else if ($type instanceof \ReflectionType) {
      $name= PHP_VERSION_ID >= 70100 ? $type->getName() : $type->__toString();

      // Check array, self and callable for more specific types, e.g. `string[]`,
      // `static` or `function(): string` in api documentation
      if ($api && ('array' === $name || 'callable' === $name || 'self' === $name) && ($s= $api(true))) {
        return self::named($s, $context);
      }
      $t= self::named($name, $context);
    } else {

      // BC with 10.4 / 10.5: delegate resolve(string, ?) -> named()
      return self::named($type, $context);
    }
    return $type->allowsNull() ? new Nullable($t) : $t;
  }

  /**
   * Resolves type name
   *
   * @param  string $type
   * @param  [:(function(string): self)] $context
   * @return self
   */
  public static function named($name, $context) {
    if ('?' === $name[0]) return new Nullable(self::named(substr($name, 1), $context));
    if ($t= self::$named[$name] ?? null) return $t;

    // * function(T): R is a function
    // * [:T] is a map 
    // * T<K, V> is a generic type definition D with K and V components
    //   except if any of K, V contains a ?, in which case it's a wild 
    //   card type.
    // * Anything else is a qualified or unqualified class name
    $l= strlen($name);
    $p= strcspn($name, '<|[*(');
    if ($p === $l) {
      return isset($context[$name]) ? $context[$name]() : ((isset($context['*']) && strcspn($name, '.\\') === $l)
        ? $context['*']($name)
        : XPClass::forName($name)
      );
    } else if ('(' === $name[0]) {
      $t= self::named(self::matching($name, '()', 0), $context);
    } else if (0 === substr_compare($name, '[:', 0, 2)) {
      $t= new MapType(self::named(self::matching($name, '[]', 1), $context));
    } else if (0 === substr_compare($name, 'function(', 0, 9)) {
      $signature= self::matching($name, '()', 8);
      if ('?' === $signature) {
        $args= null;
      } else if ('' === $signature) {
        $args= [];
      } else {
        $args= self::forNames($signature, $context);
      }
      if (false === ($p= strpos($name, ':'))) {
        $return= null;
      } else {
        $return= self::named(trim(substr($name, $p + 1)), $context);
        $name= '';
      }
      $t= new FunctionType($args, $return);
    } else if ('<' === $name[$p]) {
      $base= substr($name, 0, $p);
      $components= [];
      $wildcard= false;
      foreach (self::split(self::matching($name, '<>', $p), ',') as $arg) {
        if ('?' === $arg) {
          $components[]= Wildcard::$ANY;
          $wildcard= true;
        } else {
          $t= self::named($arg, $context);
          $wildcard= $t instanceof WildcardType;
          $components[]= $t;
        }
      }
      if ($wildcard) {
        $t= new WildcardType(self::named($base, $context), $components);
      } else if ('array' === $base) {
        $t= 1 === sizeof($components) ? new ArrayType($components[0]) : new MapType($components[1]);
      } else {
        $t= self::named($base, $context)->newGenericType($components);
      }
    } else {
      $t= self::named(trim(substr($name, 0, $p)), $context);
      $name= substr($name, $p);
    }

    // Suffixes and unions `T[]` is an array, `T*` is a vararg, `A|B|C` is a union
    while ($name) {
      if ('*' === $name[0]) {
        $t= new ArrayType($t);
        $name= trim(substr($name, 1));
      } else if ('|' === $name[0]) {
        $components= [$t];
        foreach (self::split(substr($name, 1), '|') as $arg) {
          $components[]= self::named($arg, $context);
        }
        return new TypeUnion($components);
      } else if (0 === substr_compare($name, '[]', 0, 2)) {
        $t= new ArrayType($t);
        $name= trim(substr($name, 2));
      } else {
        throw new IllegalArgumentException('Invalid type suffix '.$name);
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
