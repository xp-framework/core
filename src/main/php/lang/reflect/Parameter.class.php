<?php namespace lang\reflect;

use lang\ElementNotFoundException;
use lang\ClassFormatException;
use lang\XPClass;
use lang\Type;

/**
 * Represents a method's parameter
 *
 * @see   xp://lang.reflect.Method#getParameter
 * @see   xp://lang.reflect.Method#getParameters
 * @see   xp://lang.reflect.Method#numParameters
 * @test  xp://net.xp_framework.unittest.reflection.MethodParametersTest
 */
class Parameter extends \lang\Object {
  protected
    $_reflect = null,
    $_details = null;

  /**
   * Constructor
   *
   * @param   php.ReflectionParameter reflect
   * @param   array details
   */    
  public function __construct($reflect, $details) {
    $this->_reflect= $reflect;
    $this->_details= $details;
  }

  /**
   * Get parameter's name.
   *
   * @return  string
   */
  public function getName() {
    return $this->_reflect->getName();
  }

  /**
   * Get parameter's type.
   *
   * @return  lang.Type
   */
  public function getType() {
    try {
      if ($c= $this->_reflect->getClass()) return new XPClass($c);
    } catch (\ReflectionException $e) {
      throw new ClassFormatException(sprintf(
        'Typehint for %s::%s()\'s parameter "%s" cannot be resolved: %s',
        strtr($this->_details[0], '\\', '.'),
        $this->_details[1],
        $this->_reflect->getName(),
        $e->getMessage()
      ));
    }

    if (
      !($details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1])) ||
      !isset($details[DETAIL_ARGUMENTS][$this->_details[2]])
    ) {

      // ReflectionParameter::getType() *always* returns "array" on HHVM, this is
      // a) useless and b) inconsistent with PHP. Fall back to "var"
      if (defined('HHVM_VERSION') && $this->_reflect->isVariadic()) {
        return Type::$VAR;
      }

      // Cannot parse api doc, fall back to PHP native syntax. The reason for not doing
      // this the other way around is that we have "richer" information, e.g. "string[]",
      // where PHP simply knows about "arrays" (of whatever).
      if (XPClass::$TYPE_SUPPORTED && $t= $this->_reflect->getType()) {
        return Type::forName((string)$t);
      } else if (defined('HHVM_VERSION')) {
        return Type::forName($this->_reflect->getTypeText() ?: 'var');
      } else {
        return Type::$VAR;
      }
    }

    $t= rtrim(ltrim($details[DETAIL_ARGUMENTS][$this->_details[2]], '&'), '.');
    if ('self' === $t) {
      return new XPClass($this->_details[0]);
    } else {
      return Type::forName($t);
    }
  }

  /**
   * Get parameter's type.
   *
   * @return  string
   */
  public function getTypeName() {
    if (
      ($details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1]))
      && isset($details[DETAIL_ARGUMENTS][$this->_details[2]])
    ) {
      return ltrim($details[DETAIL_ARGUMENTS][$this->_details[2]], '&');
    } else if (XPClass::$TYPE_SUPPORTED && ($t= $this->_reflect->getType())) {
      return str_replace('HH\\', '', $t);
    } else if (defined('HHVM_VERSION')) {
      return str_replace('HH\\', '', $this->_reflect->getTypeText() ?: 'var');
    } else {
      return 'var';
    }
  }

  /**
   * Get parameter's type restriction.
   *
   * @return  lang.Type or NULL if there is no restriction
   * @throws  lang.ClassFormatException if the restriction cannot be resolved
   */
  public function getTypeRestriction() {
    try {
      if ($this->_reflect->isArray()) {
        return Type::$ARRAY;
      } else if ($this->_reflect->isCallable()) {
        return Type::$CALLABLE;
      } else if ($c= $this->_reflect->getClass()) {
        return new XPClass($c);
      } else {
        return null;
      }
    } catch (\ReflectionException $e) {
      throw new ClassFormatException(sprintf(
        'Typehint for %s::%s()\'s parameter "%s" cannot be resolved: %s',
        strtr($this->_details[0], '\\', '.'),
        $this->_details[1],
        $this->_reflect->getName(),
        $e->getMessage()
      ));
    }
  }

  /**
   * Retrieve whether this argument is optional
   *
   * @return  bool
   */
  public function isOptional() {
    return $this->_reflect->isOptional() || (defined('HHVM_VERSION') && $this->_reflect->isVariadic());
  }

  /**
   * Retrieve whether this argument is variadic
   *
   * @return  bool
   */
  public function isVariadic() {
    if ($this->_reflect->isVariadic()) {
      return true;
    } else if (
      $this->_reflect->isOptional() &&
      ($details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1])) &&
      isset($details[DETAIL_ARGUMENTS][$this->_details[2]])
    ) {
      return 0 === substr_compare($details[DETAIL_ARGUMENTS][$this->_details[2]], '...', -3);
    } else {
      return false;
    }
  }

  /**
   * Get default value.
   *
   * @throws  lang.IllegalStateException in case this argument is not optional
   * @return  var
   */
  public function getDefaultValue() {
    if ($this->_reflect->isOptional() || (defined('HHVM_VERSION') && $this->_reflect->isVariadic())) {
      return $this->_reflect->isDefaultValueAvailable() ? $this->_reflect->getDefaultValue() : null;
    }

    throw new \lang\IllegalStateException('Parameter "'.$this->_reflect->getName().'" has no default value');
  }

  /**
   * Check whether an annotation exists
   *
   * @param   string name
   * @param   string key default NULL
   * @return  bool
   */
  public function hasAnnotation($name, $key= null) {
    $n= '$'.$this->_reflect->getName();
    if (
      !($details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1])) ||
      !isset($details[DETAIL_TARGET_ANNO][$n])
    ) {   // Unknown or unparseable
      return false;
    }

    return $details && ($key 
      ? array_key_exists($key, (array)@$details[DETAIL_TARGET_ANNO][$n][$name]) 
      : array_key_exists($name, (array)@$details[DETAIL_TARGET_ANNO][$n])
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
    $n= '$'.$this->_reflect->getName();
    if (
      !($details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1])) ||
      !isset($details[DETAIL_TARGET_ANNO][$n]) || !($key 
        ? array_key_exists($key, (array)@$details[DETAIL_TARGET_ANNO][$n][$name]) 
        : array_key_exists($name, (array)@$details[DETAIL_TARGET_ANNO][$n])
      )
    ) {
      throw new ElementNotFoundException('Annotation "'.$name.($key ? '.'.$key : '').'" does not exist');
    }

    return ($key 
      ? $details[DETAIL_TARGET_ANNO][$n][$name][$key] 
      : $details[DETAIL_TARGET_ANNO][$n][$name]
    );
  }

  /**
   * Retrieve whether a method has annotations
   *
   * @return  bool
   */
  public function hasAnnotations() {
    $n= '$'.$this->_reflect->getName();
    if (
      !($details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1])) ||
      !isset($details[DETAIL_TARGET_ANNO][$n])
    ) {   // Unknown or unparseable
      return false;
    }
    return $details ? !empty($details[DETAIL_TARGET_ANNO][$n]) : false;
  }

  /**
   * Retrieve all of a method's annotations
   *
   * @return  var[] annotations
   */
  public function getAnnotations() {
    $n= '$'.$this->_reflect->getName();
    if (
      !($details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1])) ||
      !isset($details[DETAIL_TARGET_ANNO][$n])
    ) {   // Unknown or unparseable
      return [];
    }
    return $details[DETAIL_TARGET_ANNO][$n];
  }
  
  /**
   * Creates a string representation
   *
   * @return  string
   */
  public function toString() {
    return sprintf(
      '%s<%s %s%s>',
      nameof($this),
      $this->getType()->toString(),
      $this->_reflect->getName(),
      $this->_reflect->isOptional() ? '= '.\xp::stringOf($this->_reflect->getDefaultValue()) : ''
    );
  }
}
