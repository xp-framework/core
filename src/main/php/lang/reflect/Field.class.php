<?php namespace lang\reflect;

use lang\{
  ClassLoader,
  ElementNotFoundException,
  IllegalAccessException,
  IllegalArgumentException,
  Type,
  Value,
  XPClass,
  XPException
};

/**
 * Represents a class field
 *
 * @test  xp://net.xp_framework.unittest.reflection.FieldsTest
 * @see   xp://lang.XPClass
 */
class Field implements Value {
  protected $accessible= false;
  protected $_class= null;
  public $_reflect= null;

  /**
   * Constructor
   *
   * @param   string class
   * @param   php.ReflectionProperty reflect
   */    
  public function __construct($class, $reflect) {
    $this->_class= $class;
    $this->_reflect= $reflect;
  }

  /** Get field's name */
  public function getName(): string { return $this->_reflect->getName(); }
  
  /** Gets field type */
  public function getType(): Type {
    if ($details= XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName())) {
      if (isset($details[DETAIL_RETURNS])) {
        $type= $details[DETAIL_RETURNS];
      } else if (isset($details[DETAIL_ANNOTATIONS]['type'])) {
        $type= $details[DETAIL_ANNOTATIONS]['type'];
      } else {
        return Type::$VAR;
      }

      if ('self' === $type) {
        return new XPClass($this->_reflect->getDeclaringClass());
      } else {
        return Type::forName($type);
      }
    }
    return Type::$VAR;
  }

  /** Gets field type's name */
  public function getTypeName(): string {
    if ($details= XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName())) {
      if (isset($details[DETAIL_RETURNS])) {
        return $details[DETAIL_RETURNS];
      } else if (isset($details[DETAIL_ANNOTATIONS]['type'])) {
        return $details[DETAIL_ANNOTATIONS]['type'];
      }
    }
    return 'var';
  }

  /**
   * Check whether an annotation exists
   *
   * @param   string name
   * @param   string key default NULL
   * @return  bool
   */
  public function hasAnnotation($name, $key= null): bool {
    $details= XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    if ($key) {
      $a= $details[DETAIL_ANNOTATIONS][$name] ?? null;
      return is_array($a) && array_key_exists($key, $a);
    } else {
      return array_key_exists($name, $details[DETAIL_ANNOTATIONS] ?? []);
    }
  }

  /**
   * Retrieve annotation by name
   *
   * @param   string name
   * @param   string key default NULL
   * @return  var
   * @throws  lang.ElementNotFoundException
   */
  public function getAnnotation($name, $key= null) {
    $details= XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    if ($key) {
      $a= $details[DETAIL_ANNOTATIONS][$name] ?? null;
      if (is_array($a) && array_key_exists($key, $a)) return $a[$key];
    } else {
      if (array_key_exists($name, $details[DETAIL_ANNOTATIONS] ?? [])) return $details[DETAIL_ANNOTATIONS][$name];
    }

    throw new ElementNotFoundException('Annotation "'.$name.($key ? '.'.$key : '').'" does not exist');
  }
  
  /** Retrieve whether this field has annotations */
  public function hasAnnotations(): bool {
    $details= XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    return $details ? !empty($details[DETAIL_ANNOTATIONS]) : false;
  }

  /**
   * Retrieve all of this field's annotations
   *
   * @return [:var] annotations
   */
  public function getAnnotations() {
    $details= XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    return $details ? $details[DETAIL_ANNOTATIONS] : [];
  }

  /**
   * Returns the XPClass object representing the class or interface 
   * that declares the field represented by this Field object.
   */
  public function getDeclaringClass(): XPClass {
    return new XPClass($this->_reflect->getDeclaringClass());
  }
  
  /**
   * Returns the value of the field represented by this Field, on the 
   * specified object.
   *
   * @param   object instance
   * @return  var  
   * @throws  lang.IllegalArgumentException in case the passed object is not an instance of the declaring class
   * @throws  lang.IllegalAccessException in case this field is not public
   */
  public function get($instance) {
    if (null !== $instance && !($instance instanceof $this->_class)) {
      $d= $this->_reflect->getDeclaringClass();
      if (!$d->isTrait()) {
        throw new IllegalArgumentException(sprintf(
          'Passed argument is not a %s class (%s)',
          XPClass::nameOf($this->_class),
          nameof($instance)
        ));
      }

      $o= new \ReflectionObject($instance);
      if (!in_array($d->getName(), $o->getTraitNames())) {
        throw new IllegalArgumentException(sprintf(
          'Passed argument does not use the trait %s (%s)',
          XPClass::nameOf($this->_class),
          nameof($instance)
        ));
      }
      $target= $o->getProperty($this->_reflect->getName());
    } else {
      $target= $this->_reflect;
    }


    // Check modifiers. If scope is an instance of this class, allow
    // protected method invocation (which the PHP reflection API does 
    // not).
    $m= $target->getModifiers();
    $public= $m & MODIFIER_PUBLIC;
    if (!$public && !$this->accessible) {
      $t= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
      $scope= $t[1]['class'] ?? ClassLoader::getDefault()->loadUri($t[0]['file'])->literal();
      $decl= $target->getDeclaringClass()->getName();
      if ($m & MODIFIER_PROTECTED) {
        $allow= $scope === $decl || is_subclass_of($scope, $decl);
      } else {
        $allow= $scope === $decl;
      }

      if (!$allow) {
        throw new IllegalAccessException(sprintf(
          'Cannot read %s %s::$%s from scope %s',
          Modifiers::stringOf($this->getModifiers()),
          $this->_class,
          $target->getName(),
          $scope
        ));
      }
    }

    try {
      $public || $target->setAccessible(true);
      return $target->getValue($instance);
    } catch (Throwable $e) {
      throw $e;
    } catch (\Throwable $e) {
      throw new XPException($e->getMessage());
    }
  }

  /**
   * Changes the value of the field represented by this Field, on the 
   * specified object.
   *
   * @param   object instance
   * @param   var value
   * @throws  lang.IllegalArgumentException in case the passed object is not an instance of the declaring class
   * @throws  lang.IllegalAccessException in case this field is not public
   */
  public function set($instance, $value) {
    if (null !== $instance && !($instance instanceof $this->_class)) {
      $d= $this->_reflect->getDeclaringClass();
      if (!$d->isTrait()) {
        throw new IllegalArgumentException(sprintf(
          'Passed argument is not a %s class (%s)',
          XPClass::nameOf($this->_class),
          nameof($instance)
        ));
      }

      $o= new \ReflectionObject($instance);
      if (!in_array($d->getName(), $o->getTraitNames())) {
        throw new IllegalArgumentException(sprintf(
          'Passed argument does not use the trait %s (%s)',
          XPClass::nameOf($this->_class),
          nameof($instance)
        ));
      }
      $target= $o->getProperty($this->_reflect->getName());
    } else {
      $target= $this->_reflect;
    }
  
    // Check modifiers. If scope is an instance of this class, allow
    // protected method invocation (which the PHP reflection API does 
    // not).
    $m= $target->getModifiers();
    $public= $m & MODIFIER_PUBLIC;
    if (!$public && !$this->accessible) {
      $t= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
      $scope= $t[1]['class'] ?? ClassLoader::getDefault()->loadUri($t[0]['file'])->literal();
      $decl= $target->getDeclaringClass()->getName();
      if ($m & MODIFIER_PROTECTED) {
        $allow= $scope === $decl || is_subclass_of($scope, $decl);
      } else {
        $allow= $scope === $decl;
      }
      if (!$allow) {
        throw new IllegalAccessException(sprintf(
          'Cannot write %s %s::$%s from scope %s',
          Modifiers::stringOf($this->getModifiers()),
          XPClass::nameOf($this->_class),
          $target->getName(),
          $scope
        ));
      }
    }

    try {
      $public || $target->setAccessible(true);
      return $target->setValue($instance, $value);
    } catch (Throwable $e) {
      throw $e;
    } catch (\Throwable $e) {
      throw new XPException($e->getMessage());
    }
  }

  /** Retrieve this field's modifiers */    
  public function getModifiers(): int { return $this->_reflect->getModifiers(); }

  /**
   * Sets whether this routine should be accessible from anywhere, 
   * regardless of its visibility level.
   */
  public function setAccessible(bool $flag): self {
    $this->accessible= $flag;
    return $this;
  }

  /** Compares this field to another value */
  public function compareTo($value): int {
    if (!($value instanceof self)) return 1;
    if (0 !== ($c= $value->_reflect->getName() <=> $this->_reflect->getName())) return $c;
    if (0 !== ($c= $value->getDeclaringClass()->compareTo($this->getDeclaringClass()))) return $c;
    return 0;
  }

  /** Returns a hashcode for this routine */
  public function hashCode(): string {
    return 'F['.$this->_reflect->getDeclaringClass().$this->_reflect->getName();
  }
  
  /** Creates a string representation of this field */
  public function toString(): string {
    return sprintf(
      '%s %s %s::$%s',
      Modifiers::stringOf($this->getModifiers()),
      $this->getTypeName(),
      $this->getDeclaringClass()->getName(),
      $this->getName()
    );
  }
}
