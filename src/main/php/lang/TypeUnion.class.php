<?php namespace lang;

/**
 * Represents a union of types
 *
 * @see   lang.Type
 * @test  lang.unittest.TypeUnionTest
 */
class TypeUnion extends Type {
  private $types;

  static function __static() { }

  /**
   * Creates a new type union instance
   *
   * @param  lang.Type[] $types
   * @throws lang.IllegalArgumentException
   */
  public function __construct(array $types) {
    if (sizeof($types) < 2) {
      throw new IllegalArgumentException('A type union consists of at least 2 types');
    }
    $this->types= $types;
    parent::__construct(implode('|', array_map(fn($type) => $type->getName(), $types)), null);
  }

  /** @return lang.Type[] */
  public function types() { return $this->types; }

  /** Returns type literal */
  public function literal(): string {
    return "\xb5".implode("\xb8", array_map(fn($type) => $type->literal(), $this->types));
  }

  /** Determines whether the specified object is an instance of this type */
  public function isInstance($obj): bool {
    foreach ($this->types as $type) {
      if ($type->isInstance($obj)) return true;
    }
    return false;
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var... $args
   * @return  var
   */
  public function newInstance(... $args) {
    $value= $args[0] ?? null;
    foreach ($this->types as $type) {
      if ($type->isInstance($value)) return $type->newInstance($value);
    }

    throw new IllegalArgumentException('Cannot create instances of the '.$this->getName().' type from '.typeof($value)->getName());
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
      foreach ($this->types as $type) {
        if ($type->isInstance($value)) return $type->cast($value);
      }
    }

    throw new ClassCastException('Cannot cast to the '.$this->getName().' type from '.typeof($value)->getName());
  }

  /**
   * Get a type instance for a given name
   *
   * @param   string name
   * @return  self
   * @throws  lang.IllegalArgumentException if the given name does not correspond to a function type
   */
  public static function forName($name) {
    $t= parent::forName($name);
    if ($t instanceof self) return $t;

    throw new IllegalArgumentException($name.' is not a union type');
  }

  /**
   * Tests whether this type is assignable from another type
   *
   * ```php
   * $union= TypeUnion::forName('int|string|lang.Throwable');
   *
   * // It's assignable to each of its components
   * $union->isAssignableFrom('int')                          // TRUE
   * $union->isAssignableFrom('string')                       // TRUE
   * $union->isAssignableFrom('lang.XPException')             // TRUE
   * $union->isAssignableFrom('bool')                         // FALSE
   *
   * // It's assignable to unions if the union consists completely
   * // of types assignable to types in this union.
   * $union->isAssignableFrom('int|string')                   // TRUE
   * $union->isAssignableFrom('int|lang.XPException')         // TRUE
   * $union->isAssignableFrom('int|string|lang.XPException')  // TRUE
   * $union->isAssignableFrom('int|bool')                     // FALSE
   * $union->isAssignableFrom('int|string|bool')              // FALSE
   * $union->isAssignableFrom('int|string|util.Date')         // FALSE
   * ```
   *
   * @param   var $from Either a type or a type name
   * @return  bool
   */
  public function isAssignableFrom($from): bool {
    $t= $from instanceof Type ? $from : Type::forName($from);
    if ($t instanceof self) {
      foreach ($t->types as $type) {
        if (!$this->isAssignableFrom($type)) return false;
      }
      return true;
    } else {
      foreach ($this->types as $type) {
        if ($type->isAssignableFrom($t)) return true;
      }
      return false;
    }
  }

  /**
   * Returns a sorted list of the given types' names.
   *
   * @param  parent[] $types
   * @return string[]
   */
  private function sorted($types) {
    $n= [];
    foreach ($types as $type) {
      $n[]= $type->name;
    }
    sort($n);
    return $n;
  }

  /** Compares to another value */
  public function compareTo($value): int {
    return $value instanceof self ? $this->sorted($this->types) <=> $this->sorted($value->types) : 1;
  }

  /** Checks for equality with another value */
  public function equals($value): bool {
    return $value instanceof static && $this->sorted($this->types) === $this->sorted($value->types);
  }
}
