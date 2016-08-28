<?php namespace lang;

/**
 * Represents map types
 *
 * @see   xp://lang.Type
 * @test  xp://net.xp_framework.unittest.core.MapTypeTest
 */
class MapType extends Type {
  private $component;

  static function __static() { }

  /**
   * Creates a new array type instance
   *
   * @param  string|lang.Type $component
   */
  public function __construct($component) {
    if ($component instanceof Type) {
      $this->component= $component;
      parent::__construct('[:'.$component->getName().']', []);
    } else {
      $this->component= Type::forName($component);
      parent::__construct('[:'.$component.']', []);
    }
  }

  /** Gets this array's component type */
  public function componentType(): Type { return $this->component; }

  /** Returns type literal */
  public function literal(): string { return "\xbb".$this->component->literal(); }

  /**
   * Get a type instance for a given name
   *
   * @param   string name
   * @return  lang.ArrayType
   * @throws  lang.IllegalArgumentException if the given name does not correspond to a primitive
   */
  public static function forName($name) {
    if ('[:' !== substr($name, 0, 2)) {
      throw new IllegalArgumentException('Not a map: '.$name);
    }
    
    return new self(substr($name, 2, -1));
  }

  /** Determines whether the specified object is an instance of this type */
  public function isInstance($obj): bool {
    if (!is_array($obj)) return false;

    foreach ($obj as $k => $element) {
      if (is_int($k) || !$this->component->isInstance($element)) return false;
    }
    return true;
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var value
   * @return  var
   */
  public function newInstance($value= null) {
    if (null === $value) {
      return [];
    } else if (is_array($value)) {
      $self= [];
      foreach ($value as $k => $element) {
        if (is_int($k)) throw new IllegalArgumentException('Cannot create instances of the '.$this->getName().' type from var[]');
        $self[$k]= $this->component->cast($element);
      }
      return $self;
    } else {
      throw new IllegalArgumentException('Cannot create instances of the '.$this->getName().' type from '.\xp::typeOf($value));
    }
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
    } else if (is_array($value)) {
      foreach ($value as $k => $element) {
        if (is_int($k)) throw new ClassCastException('Cannot cast to the '.$this->getName().' type from var[]');
        $value[$k]= $this->component->cast($element);
      }
      return $value;
    } else {
      throw new ClassCastException('Cannot cast to the '.$this->getName().' type from '.\xp::typeOf($value));
    }
  }

  /** Tests whether this type is assignable from another type */
  public function isAssignableFrom($type): bool {
    $t= $type instanceof Type ? $type : Type::forName($type);
    return $t instanceof self && $t->component->isAssignableFrom($this->component);
  }
}
