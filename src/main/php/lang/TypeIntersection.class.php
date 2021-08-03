<?php namespace lang;

/**
 * Represents a Intersection of types
 *
 * @see   xp://lang.Type
 * @test  xp://net.xp_framework.unittest.core.TypeIntersectionTest
 */
class TypeIntersection extends Type {
  private $types;

  static function __static() { }

  /**
   * Creates a new type untersection instance
   *
   * @param  lang.Type[] $types
   * @throws lang.IllegalArgumentException
   */
  public function __construct(array $types) {
    if (sizeof($types) < 2) {
      throw new IllegalArgumentException('A type intersection consists of at least 2 types');
    }
    $this->types= $types;
    parent::__construct(implode('&', array_map(function($type) { return $type->getName(); }, $types)), null);
  }

  /** @return lang.Type[] */
  public function types() { return $this->types; }

  /** Returns type literal */
  public function literal(): string {
    return "\xb5".implode("\xb9", array_map(function($type) { return $type->literal(); }, $this->types));
  }

  /** Determines whether the specified object is an instance of this type */
  public function isInstance($obj): bool {
    foreach ($this->types as $type) {
      if (!$type->isInstance($obj)) return false;
    }
    return true;
  }

  /**
   * Returns a new instance of this type
   *
   * @param   var... $args
   * @return  var
   */
  public function newInstance(... $args) {
    $value= $args[0] ?? null;
    foreach ($this->types as $type) {
      if (!$type->isInstance($value)) {
        throw new IllegalArgumentException('Cannot create instances of the '.$this->getName().' type from '.typeof($value)->getName());
      }
    }
    return clone $value;
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
        if (!$type->isInstance($value)) {
          throw new ClassCastException('Cannot cast to the '.$this->getName().' type from '.typeof($value)->getName());
        }
      }
    }
    return $type->cast($value);
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

    throw new IllegalArgumentException($name.' is not an intersection type');
  }

  /**
   * Tests whether this type is assignable from another type
   *
   * ```php
   * $t= TypeIntersection::forName('Countable&Traversable');
   *
   * // It's assignable to intersections if the intersection consists completely
   * // of types assignable to types in this intersection.
   * $intersection->isAssignableFrom('Countable&Traversable')        // TRUE
   * $intersection->isAssignableFrom('Countable&IteratorAggregate')  // TRUE
   * ```
   *
   * @param   var $from Either a type or a type name
   * @return  bool
   */
  public function isAssignableFrom($from): bool {
    $t= $from instanceof Type ? $from : Type::forName($from);
    if (!($t instanceof self)) return false;

    $assignableFrom= function($type) {
      foreach ($this->types as $compare) {
        echo $type, " isAssignableFrom ", $compare, "\n";
        if ($type->isAssignableFrom($compare)) return true;
      }
      return false;
    };
    foreach ($t->types as $type) {
      if (!$assignableFrom($type)) return false;
    } 
    return true;
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
