<?php namespace lang;

/**
 * Represents wildcard types
 *
 * @see   xp://lang.Type
 * @test  xp://net.xp_framework.unittest.core.WildcardTypeTest
 */
class WildcardType extends Type {
  protected $base;
  protected $components;

  static function __static() { }

  /**
   * Creates a new array type instance
   *
   * @param  lang.XPClass $base
   * @param  lang.Type[] $components
   */
  public function __construct(XPClass $base, array $components) {
    $this->base= $base;
    $this->components= $components;
    parent::__construct(sprintf(
      '%s<%s>',
      $base->getName(),
      implode(',', array_map(function($e) { return $e->getName(); }, $components))
    ), null);
  }

  /** Returns base type */
  public function base(): XPClass { return $this->base; }

  /** @return lang.Type[] */
  public function components() { return $this->components; }

  /**
   * Get a type instance for a given name
   *
   * @param   string name
   * @return  lang.ArrayType
   * @throws  lang.IllegalArgumentException if the given name does not correspond to a wildcard type
   */
  public static function forName($name) {
    if (false === strpos($name, '?') || false === ($p= strpos($name, '<'))) {
      throw new IllegalArgumentException('Not a wildcard type: '.$name);
    }

    $t= parent::forName($name);
    if ($t instanceof self) return $t;

    throw new IllegalArgumentException($name.' is not a wildcard type');
  }

  /** Returns type literal */
  public function literal(): string {
    throw new IllegalStateException('Wildcard types cannot be used in type literals');
  }

  /**
   * Helper method for isInstance() and isAssignableFrom()
   *
   * @param  lang.XPClass $class
   * @return bool
   */
  protected function assignableFromClass($class) {
    if ($class->isGeneric() && $this->base->isAssignableFrom($class->genericDefinition())) {
      foreach ($class->genericArguments() as $pos => $arg) {
        if (!$this->components[$pos]->isAssignableFrom($arg)) return false;
      }
      return true;
    }
    return false;
  }

  /** Determines whether the specified object is an instance of this type */
  public function isInstance($obj): bool {
    $t= typeof($obj);
    return $t instanceof XPClass && $this->assignableFromClass($t);
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var... $args
   * @return  var
   */
  public function newInstance(... $args) {
    throw new IllegalAccessException('Cannot instantiate wildcard types');
  }

  /**
   * Cast a value to this type
   *
   * @param   var value
   * @return  var
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    $t= typeof($value);
    if ($t instanceof XPClass && $this->assignableFromClass($t)) {
      return $value;
    }
    throw new ClassCastException('Cannot cast '.typeof($value)->getName().' to the '.$this->getName().' type');
  }

  /** Tests whether this type is assignable from another type */
  public function isAssignableFrom($type): bool {
    $t= $type instanceof Type ? $type : Type::forName($type);
    return $t instanceof XPClass && $this->assignableFromClass($t);
  }
}
