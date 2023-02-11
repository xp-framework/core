<?php namespace lang;

/**
 * Represents array types
 *
 * @see   xp://lang.Type
 * @test  xp://net.xp_framework.unittest.core.ArrayTypeTest
 */
class ArrayType extends Type {
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
      parent::__construct($component->getName().'[]', []);
    } else {
      $this->component= Type::forName($component);
      parent::__construct($component.'[]', []);
    }
  }

  /** Gets this array's component type */
  public function componentType(): Type { return $this->component; }

  /** Returns type literal */
  public function literal(): string { return $this->component->literal()."\x91\x92"; }

  /**
   * Get a type instance for a given name
   *
   * @param   string name
   * @return  lang.ArrayType
   * @throws  lang.IllegalArgumentException if the given name does not correspond to a primitive
   */
  public static function forName($name) {
    if ('[]' !== substr($name, -2)) {
      throw new IllegalArgumentException('Not an array: '.$name);
    }
    
    return new self(substr($name, 0, -2));
  }

  /**
   * Determines whether the specified object is an instance of this
   * type. 
   *
   * @param   var obj
   * @return  bool
   */
  public function isInstance($obj): bool {
    if (!is_array($obj)) return false;

    foreach ($obj as $k => $element) {
      if (!is_int($k) || !$this->component->isInstance($element)) return false;
    }
    return true;
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var... $args
   * @return  var
   */
  public function newInstance(... $args) {
    if (empty($args) || null === $args[0]) {
      return [];
    } else if (1 === sizeof($args)) {
      $array= $args[0];
    } else {
      $array= $args;
    }

    if (!is_array($array)) {
      throw new IllegalArgumentException('Cannot create instances of the '.$this->getName().' type from '.typeof($array)->getName());
    }

    $self= [];
    foreach ($array as $i => $element) {
      if (!is_int($i)) throw new IllegalArgumentException('Cannot create instances of the '.$this->getName().' type from [:var]');
      $self[]= $this->component->cast($element);
    }
    return $self;
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
      foreach ($value as $i => $element) {
        if (!is_int($i)) throw new ClassCastException('Cannot cast to the '.$this->getName().' type from [:var]');
        $value[$i]= $this->component->cast($element);
      }
      return $value;
    } else {
      throw new ClassCastException('Cannot cast to the '.$this->getName().' type from '.typeof($value)->getName());
    }
  }

  /** Tests whether this type is assignable from another type */
  public function isAssignableFrom($type): bool {
    $t= $type instanceof Type ? $type : Type::forName($type);
    return $t instanceof self && $t->component->isAssignableFrom($this->component);
  }
}
