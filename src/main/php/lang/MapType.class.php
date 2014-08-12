<?php namespace lang;

/**
 * Represents map types
 *
 * @see   xp://lang.Type
 * @test  xp://net.xp_framework.unittest.core.MapTypeTest
 */
class MapType extends Type {

  /**
   * Creates a new array type instance
   *
   * @param  var component
   */
  public function __construct($component) {
    if ($component instanceof Type) {
      parent::__construct('[:'.$component->getName().']');
    } else {
      parent::__construct('[:'.$component.']');
    }
  }

  /**
   * Gets this array's component type
   *
   * @return  lang.Type
   */
  public function componentType() {
    return Type::forName(substr($this->name, 2, -1));
  }

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

  /**
   * Returns type literal
   *
   * @return  string
   */
  public function literal() {
    return '»'.$this->componentType()->literal();
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
      if (is_int($k) || !$c->isInstance($element)) return false;
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
      foreach ($value as $k => $element) {
        if (is_int($k)) raise('lang.IllegalArgumentException', 'Cannot create instances of the '.$this->getName().' type from var[]');
        $self[$k]= $c->cast($element);
      }
      return $self;
    } else {
      raise('lang.IllegalArgumentException', 'Cannot create instances of the '.$this->getName().' type from '.\xp::typeOf($value));
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
      foreach ($value as $k => $element) {
        if (is_int($k)) raise('lang.ClassCastException', 'Cannot cast to the '.$this->getName().' type from var[]');
        $value[$k]= $c->cast($element);
      }
      return $value;
    } else {
      raise('lang.ClassCastException', 'Cannot cast to the '.$this->getName().' type from '.\xp::typeOf($value));
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
