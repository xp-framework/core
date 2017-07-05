<?php namespace lang\reflect;

use lang\XPClass;
use lang\IllegalArgumentException;
use lang\IllegalAccessException;

/**
 * Represents a class field
 *
 * @test  xp://net.xp_framework.unittest.reflection.FieldsTest
 * @see   xp://lang.XPClass
 */
class Field extends \lang\Object {
  private static $read;
  private static $write;

  protected
    $accessible = false,
    $_class     = null;

  public
    $_reflect   = null;

  static function __static() {
    if (defined('HHVM_VERSION_ID')) {
      self::$read= function($class, $reflect, $instance, $public) {
        if (null === $instance) {
          return hphp_get_static_property($class, $reflect->name, !$public);
        } else {
          return hphp_get_property($instance, $class, $reflect->name);
        }
      };
      self::$write= function($class, $reflect, $instance, $value, $public) {
        if (null === $instance) {
          return hphp_set_static_property($class, $reflect->name, $value, !$public);
        } else {
          return hphp_set_property($instance, $class, $reflect->name, $value);
        }
      };
    } else {
      self::$read= function($class, $reflect, $instance, $public) {
        $public || $reflect->setAccessible(true);
        return $reflect->getValue($instance);
      };
      self::$write= function($class, $reflect, $instance, $value, $public) {
        $public || $reflect->setAccessible(true);
        return $reflect->setValue($instance, $value);
      };
    }
  }

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

  /**
   * Get field's name.
   *
   * @return  string
   */
  public function getName() {
    return $this->_reflect->getName();
  }
  
  /**
   * Gets field type
   *
   * @return  lang.Type
   */
  public function getType() {
    if ($details= \lang\XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName())) {
      if (isset($details[DETAIL_RETURNS])) {
        $type= $details[DETAIL_RETURNS];
      } else if (isset($details[DETAIL_ANNOTATIONS]['type'])) {
        $type= $details[DETAIL_ANNOTATIONS]['type'];
      } else if (defined('HHVM_VERSION')) {
        $type= $this->_reflect->getTypeText() ?: 'var';
      } else {
        return \lang\Type::$VAR;
      }

      if ('self' === $type) {
        return new \lang\XPClass($this->_reflect->getDeclaringClass());
      } else {
        return \lang\Type::forName($type);
      }
    }
    return \lang\Type::$VAR;
  }

  /**
   * Gets field type
   *
   * @return  string
   */
  public function getTypeName() {
    if ($details= \lang\XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName())) {
      if (isset($details[DETAIL_RETURNS])) {
        return $details[DETAIL_RETURNS];
      } else if (isset($details[DETAIL_ANNOTATIONS]['type'])) {
        return $details[DETAIL_ANNOTATIONS]['type'];
      } else if (defined('HHVM_VERSION')) {
        return str_replace('HH\\', '', $this->_reflect->getTypeText()) ?: 'var';
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
  public function hasAnnotation($name, $key= null) {
    $details= \lang\XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName());

    return $details && ($key 
      ? array_key_exists($key, (array)@$details[DETAIL_ANNOTATIONS][$name]) 
      : array_key_exists($name, (array)@$details[DETAIL_ANNOTATIONS])
    );
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
    $details= \lang\XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName());

    if (!$details || !($key 
      ? array_key_exists($key, @$details[DETAIL_ANNOTATIONS][$name]) 
      : array_key_exists($name, @$details[DETAIL_ANNOTATIONS])
    )) {
      throw new ElementNotFoundException('Annotation "'.$name.($key ? '.'.$key : '').'" does not exist');
    }

    return ($key 
      ? $details[DETAIL_ANNOTATIONS][$name][$key] 
      : $details[DETAIL_ANNOTATIONS][$name]
    );
  }
  
  /**
   * Retrieve whether this field has annotations
   *
   * @return  bool
   */
  public function hasAnnotations() {
    $details= \lang\XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    return $details ? !empty($details[DETAIL_ANNOTATIONS]) : false;
  }

  /**
   * Retrieve all of this field's annotations
   *
   * @return  array annotations
   */
  public function getAnnotations() {
    $details= \lang\XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    return $details ? $details[DETAIL_ANNOTATIONS] : [];
  }

  /**
   * Returns the XPClass object representing the class or interface 
   * that declares the field represented by this Field object.
   *
   * @return  lang.XPClass
   */
  public function getDeclaringClass() {
    return new \lang\XPClass($this->_reflect->getDeclaringClass()->getName());
  }
  
  /**
   * Returns the value of the field represented by this Field, on the 
   * specified object.
   *
   * @param   lang.Object instance
   * @return  var  
   * @throws  lang.IllegalArgumentException in case the passed object is not an instance of the declaring class
   * @throws  lang.IllegalAccessException in case this field is not public
   */
  public function get($instance) {
    if (null !== $instance && !($instance instanceof $this->_class)) {
      throw new \lang\IllegalArgumentException(sprintf(
        'Passed argument is not a %s class (%s)',
        \lang\XPClass::nameOf($this->_class),
        \xp::typeOf($instance)
      ));
    }

    // Check modifiers. If scope is an instance of this class, allow
    // protected method invocation (which the PHP reflection API does 
    // not).
    $m= $this->_reflect->getModifiers();
    $public= $m & MODIFIER_PUBLIC;
    if (!$public && !$this->accessible) {
      $t= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
      $scope= isset($t[1]['class']) ? $t[1]['class'] : \lang\ClassLoader::getDefault()->loadUri($t[0]['file'])->literal();
      $decl= $this->_reflect->getDeclaringClass()->getName();
      if ($m & MODIFIER_PROTECTED) {
        $allow= $scope === $decl || is_subclass_of($scope, $decl);
      } else {
        $allow= $scope === $decl;
      }

      if (!$allow) {
        throw new \lang\IllegalAccessException(sprintf(
          'Cannot read %s %s::$%s from scope %s',
          Modifiers::stringOf($this->getModifiers()),
          $this->_class,
          $this->_reflect->getName(),
          $scope
        ));
      }
    }

    try {
      return (self::$read)($this->_class, $this->_reflect, $instance, $public);
    } catch (\lang\Throwable $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new \lang\XPException($e->getMessage());
    }
  }

  /**
   * Changes the value of the field represented by this Field, on the 
   * specified object.
   *
   * @param   lang.Object instance
   * @param   var value
   * @throws  lang.IllegalArgumentException in case the passed object is not an instance of the declaring class
   * @throws  lang.IllegalAccessException in case this field is not public
   */
  public function set($instance, $value) {
    if (null !== $instance && !($instance instanceof $this->_class)) {
      throw new IllegalArgumentException(sprintf(
        'Passed argument is not a %s class (%s)',
        XPClass::nameOf($this->_class),
        \xp::typeOf($instance)
      ));
    }
  
    // Check modifiers. If scope is an instance of this class, allow
    // protected method invocation (which the PHP reflection API does 
    // not).
    $m= $this->_reflect->getModifiers();
    $public= $m & MODIFIER_PUBLIC;
    if (!$public && !$this->accessible) {
      $t= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
      $scope= isset($t[1]['class']) ? $t[1]['class'] : \lang\ClassLoader::getDefault()->loadUri($t[0]['file'])->literal();
      $decl= $this->_reflect->getDeclaringClass()->getName();
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
          $this->_reflect->getName(),
          $scope
        ));
      }
    }

    try {
      return (self::$write)($this->_class, $this->_reflect, $instance, $value, $public);
    } catch (\lang\Throwable $e) {
      throw $e;
    } catch (\Exception $e) {
      throw new \lang\XPException($e->getMessage());
    }
  }

  /**
   * Retrieve this field's modifiers
   *
   * @see     xp://lang.reflect.Modifiers
   * @return  int
   */    
  public function getModifiers() {
    return $this->_reflect->getModifiers();
  }

  /**
   * Sets whether this routine should be accessible from anywhere, 
   * regardless of its visibility level.
   *
   * @param   bool flag
   * @return  lang.reflect.Routine this
   */
  public function setAccessible($flag) {
    $this->accessible= $flag;
    return $this;
  }

  /**
   * Returns whether an object is equal to this routine
   *
   * @param   lang.Generic cmp
   * @return  bool
   */
  public function equals($cmp) {
    return (
      $cmp instanceof self && 
      $cmp->_reflect->getName() === $this->_reflect->getName() &&
      $cmp->getDeclaringClass()->equals($this->getDeclaringClass())
    );
  }

  /**
   * Returns a hashcode for this routine
   *
   * @return  string
   */
  public function hashCode() {
    return 'F['.$this->_reflect->getDeclaringClass().$this->_reflect->getName();
  }
  
  /**
   * Creates a string representation of this field
   *
   * @return  string
   */
  public function toString() {
    return sprintf(
      '%s %s %s::$%s',
      Modifiers::stringOf($this->getModifiers()),
      $this->getTypeName(),
      $this->getDeclaringClass()->getName(),
      $this->getName()
    );
  }
}
