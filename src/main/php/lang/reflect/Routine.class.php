<?php namespace lang\reflect;

use lang\ElementNotFoundException;

/**
 * Base class for methods and constructors. Note that the methods provided
 * in this class (except for getName()) are implemented using a tokenizer
 * on the class files, gathering its information from the API docs.
 *
 * This, of course, will not be as fast as if the details were provided by
 * PHP itself and will also rely on the API docs being consistent and 
 * correct.
 *
 * @test  xp://net.xp_framework.unittest.reflection.ReflectionTest
 * @see   xp://lang.reflect.Method
 * @see   xp://lang.reflect.Constructor
 * @see   http://de3.php.net/manual/en/reflectionmethod.setaccessible.php
 */
class Routine extends \lang\Object {
  protected
    $accessible = false,
    $_class     = null;

  public 
    $_reflect   = null;

  /**
   * Constructor
   *
   * @param   string class
   * @param   php.ReflectionMethod reflect
   */    
  public function __construct($class, $reflect) {
    $this->_class= $class;
    $this->_reflect= $reflect;
  }
  
  /**
   * Get routine's name.
   *
   * @return  string
   */
  public function getName() {
    return $this->_reflect->getName();
  }
  
  /**
   * Retrieve this method's modifiers
   *
   * @see     xp://lang.reflect.Modifiers
   * @return  int
   */    
  public function getModifiers() {
  
    // Note: ReflectionMethod::getModifiers() returns whatever PHP reflection 
    // returns, but the numeric value changed since 5.0.0 as the zend_function
    // struct's fn_flags now contains not only ZEND_ACC_(PPP, STATIC, FINAL,
    // ABSTRACT) but also some internal information about how this method needs
    // to be called.
    //
    // == List of fn_flags we don't want to return from this method ==
    // #define ZEND_ACC_IMPLEMENTED_ABSTRACT   0x08
    // #define ZEND_ACC_IMPLICIT_PUBLIC        0x1000
    // #define ZEND_ACC_CTOR                   0x2000
    // #define ZEND_ACC_DTOR                   0x4000
    // #define ZEND_ACC_CLONE                  0x8000
    // #define ZEND_ACC_ALLOW_STATIC           0x10000
    // #define ZEND_ACC_SHADOW                 0x20000
    // #define ZEND_ACC_DEPRECATED             0x40000
    // #define ZEND_ACC_IMPLEMENT_INTERFACES   0x80000
    // #define ZEND_ACC_CLOSURE                0x100000
    // #define ZEND_ACC_CALL_VIA_HANDLER       0x200000
    // #define ZEND_ACC_IMPLEMENT_TRAITS       0x400000
    // #define ZEND_HAS_STATIC_IN_METHODS      0x800000
    // #define ZEND_ACC_PASS_REST_BY_REFERENCE 0x1000000
    // #define ZEND_ACC_PASS_REST_PREFER_REF   0x2000000
    // #define ZEND_ACC_RETURN_REFERENCE       0x4000000
    // #define ZEND_ACC_DONE_PASS_TWO          0x8000000
    // #define ZEND_ACC_HAS_TYPE_HINTS         0x10000000
    // ==
    return $this->_reflect->getModifiers() & ~0x1fb7f008;
  }
  
  /**
   * Returns this method's parameters
   *
   * @return  lang.reflect.Parameter[]
   */
  public function getParameters() {
    $r= [];
    $c= $this->_reflect->getDeclaringClass()->getName();
    foreach ($this->_reflect->getParameters() as $offset => $param) {
      $r[]= new Parameter($param, [$c, $this->_reflect->getName(), $offset]);
    }
    return $r;
  }

  /**
   * Retrieve one of this method's parameters by its offset
   *
   * @param   int offset
   * @return  lang.reflect.Parameter or NULL if it does not exist
   */
  public function getParameter($offset) {
    $list= $this->_reflect->getParameters();
    return isset($list[$offset]) 
      ? new Parameter($list[$offset], [$this->_reflect->getDeclaringClass()->getName(), $this->_reflect->getName(), $offset])
      : null
    ;
  }
  
  /**
   * Retrieve how many parameters this method declares (including optional 
   * ones)
   *
   * @return  int
   */
  public function numParameters() {
    return $this->_reflect->getNumberOfParameters();
  }

  /**
   * Retrieve return type
   *
   * @return  lang.Type
   */
  public function getReturnType() {
    if (
      ($details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName()))
      && isset($details[DETAIL_RETURNS])
    ) {
      $t= ltrim($details[DETAIL_RETURNS], '&');
      if ('self' === $t) {
        return new \lang\XPClass($this->_reflect->getDeclaringClass());
      } else {
        return \lang\Type::forName($t);
      }
    } else if (\lang\XPClass::$TYPE_SUPPORTED && ($t= $this->_reflect->getReturnType())) {
      return \lang\Type::forName((string)$t);
    } else if (defined('HHVM_VERSION')) {
      return \lang\Type::forName($this->_reflect->getReturnTypeText() ?: 'var');
    } else {
      return \lang\Type::$VAR;
    }
  }

  /**
   * Retrieve return type name
   *
   * @return  string
   */
  public function getReturnTypeName() {
    if (
      ($details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName()))
      && isset($details[DETAIL_RETURNS])
    ) {
      return ltrim($details[DETAIL_RETURNS], '&');
    } else if (\lang\XPClass::$TYPE_SUPPORTED && ($t= $this->_reflect->getReturnType())) {
      return str_replace('HH\\', '', $t);
    } else if (defined('HHVM_VERSION')) {
      return str_replace('HH\\', '', $this->_reflect->getReturnTypeText() ?: 'var');
    } else {
      return 'var';
    }
  }

  /**
   * Retrieve exception names
   *
   * @return  string[]
   */
  public function getExceptionNames() {
    $details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    return $details ? $details[DETAIL_THROWS] : [];
  }

  /**
   * Retrieve exception types
   *
   * @return  lang.XPClass[]
   */
  public function getExceptionTypes() {
    $details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    if (!$details) return [];

    $thrown= [];
    foreach ($details[DETAIL_THROWS] as $name) {
      $thrown[]= '\\' === $name{0} ? new \lang\XPClass(substr($name, 1)) : \lang\XPClass::forName($name);
    }
    return $thrown;
  }
  
  /**
   * Returns the XPClass object representing the class or interface 
   * that declares the method represented by this Method object.
   *
   * @return  lang.XPClass
   */
  public function getDeclaringClass() {
    return new \lang\XPClass($this->_reflect->getDeclaringClass());
  }
  
  /**
   * Retrieves the api doc comment for this method. Returns NULL if
   * no documentation is present.
   *
   * @return  string
   */
  public function getComment() {
    if (!($details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName()))) return null;
    return $details[DETAIL_COMMENT];
  }
  
  /**
   * Check whether an annotation exists
   *
   * @param   string name
   * @param   string key default NULL
   * @return  bool
   */
  public function hasAnnotation($name, $key= null) {
    $details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());

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
    $details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
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
   * Retrieve whether a method has annotations
   *
   * @return  bool
   */
  public function hasAnnotations() {
    $details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    return $details ? !empty($details[DETAIL_ANNOTATIONS]) : false;
  }

  /**
   * Retrieve all of a method's annotations
   *
   * @return  array annotations
   */
  public function getAnnotations() {
    $details= \lang\XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    return $details ? $details[DETAIL_ANNOTATIONS] : [];
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
    return 'R['.$this->_reflect->getDeclaringClass().$this->_reflect->getName();
  }
  
  /**
   * Retrieve string representation. Examples:
   *
   * <pre>
   *   public lang.XPClass getClass()
   *   public static util.Date now()
   *   public open(string $mode) throws io.FileNotFoundException, io.IOException
   * </pre>
   *
   * @return  string
   */
  public function toString() {
    $signature= '';
    foreach ($this->getParameters() as $param) {
      if ($param->isOptional()) {
        $signature.= ', ['.$param->getTypeName().' $'.$param->getName().'= '.str_replace("\n", ' ', \xp::stringOf($param->getDefaultValue())).']';
      } else {
        $signature.= ', '.$param->getTypeName().' $'.$param->getName();
      }
    }
    if ($exceptions= $this->getExceptionNames()) {
      $throws= ' throws '.implode(', ', $exceptions);
    } else {
      $throws= '';
    }
    return sprintf(
      '%s %s %s(%s)%s',
      Modifiers::stringOf($this->getModifiers()),
      $this->getReturnTypeName(),
      $this->getName(),
      substr($signature, 2),
      $throws
    );
  }
}
