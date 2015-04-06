<?php namespace lang;

/**
 * Represents array types
 *
 * @see   xp://lang.Type
 * @test  xp://net.xp_framework.unittest.core.ArrayTypeTest
 */
class ArrayType extends Type {

  static function __static() { }

  /**
   * Creates a new array type instance
   *
   * @param  var component
   */
  public function __construct($component) {
    if ($component instanceof Type) {
      parent::__construct($component->getName().'[]', []);
    } else {
      parent::__construct($component.'[]', []);
    }
  }

  /**
   * Gets this array's component type
   *
   * @return  lang.Type
   */
  public function componentType() {
    return Type::forName(substr($this->name, 0, strpos($this->name, '[')));
  }

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
   * Returns type literal
   *
   * @return  string
   */
  public function literal() {
    return '¦'.$this->componentType()->literal();
  }

  /**
   * Determines whether the specified object is an instance of this
   * type. 
   *
   * @param   var obj
   * @return  bool
   */
  public function isInstance($obj) {
    if (!is_array($obj)) return false;

    $c= $this->componentType();
    foreach ($obj as $k => $element) {
      if (!is_int($k) || !$c->isInstance($element)) return false;
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
      $c= $this->componentType();
      foreach ($value as $i => $element) {
        if (!is_int($i)) throw new IllegalArgumentException('Cannot create instances of the '.$this->getName().' type from [:var]');
        $self[]= $c->cast($element);
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
      $c= $this->componentType();
      foreach ($value as $i => $element) {
        if (!is_int($i)) throw new ClassCastException('Cannot cast to the '.$this->getName().' type from [:var]');
        $value[$i]= $c->cast($element);
      }
      return $value;
    } else {
      throw new ClassCastException('Cannot cast to the '.$this->getName().' type from '.\xp::typeOf($value));
    }
  }

  /**
   * Tests whether this type is assignable from another type
   *
   * @param   var type
   * @return  bool
   */
  public function isAssignableFrom($type) {
    $t= $type instanceof Type ? $type : Type::forName($type);
    return $t instanceof self 
      ? $t->componentType()->isAssignableFrom($this->componentType())
      : false
    ;
  }
}
