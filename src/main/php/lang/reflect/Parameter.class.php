<?php namespace lang\reflect;

use lang\{ElementNotFoundException, ClassLoadingException, ClassFormatException, XPClass, Type, TypeUnion};
use util\Objects;

/**
 * Represents a method's parameter
 *
 * @see   xp://lang.reflect.Method#getParameter
 * @see   xp://lang.reflect.Method#getParameters
 * @see   xp://lang.reflect.Method#numParameters
 * @test  xp://net.xp_framework.unittest.reflection.MethodParametersTest
 */
class Parameter {
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
   * Resolve name, handling `self` and `parent` (`static` is only for return
   * types, see https://wiki.php.net/rfc/static_return_type#allowed_positions).
   *
   * @param  string $name
   * @return lang.Type
   */
  private function resolve($name) {
    if ('self' === $name) {
      return new XPClass($this->_reflect->getDeclaringClass());
    } else if ('parent' === $name) {
      return new XPClass($this->_reflect->getDeclaringClass()->getParentClass());
    } else {
      return Type::forName($name);
    }
  }

  /**
   * Get parameter's type.
   *
   * @return lang.Type
   * @throws  lang.ClassFormatException if the restriction cannot be resolved
   */
  public function getType() {
    $t= $this->getTypeRestriction();

    if (null === $t) {
      // Check for type in api documentation
      $t= Type::$VAR;
    } else if (Type::$ARRAY === $t) {
      // Check for more specific type, e.g. `string[]` in api documentation
    } else {
      return $t;
    }

    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1]);
    $r= $details[DETAIL_ARGUMENTS][$this->_details[2]] ?? null;
    return null === $r ? $t : $this->resolve(rtrim(ltrim($r, '&'), '.'));
  }

  /**
   * Get parameter's type name
   *
   * @return string
   */
  public function getTypeName() {
    static $map= [
      'mixed'   => 'var',
      'false'   => 'bool',
      'boolean' => 'bool',
      'double'  => 'float',
      'integer' => 'int',
    ];

    $t= $this->_reflect->getType();
    if (null === $t) {
      // Check for type in api documentation
      $name= 'var';
    } else if ($t instanceof \ReflectionUnionType) {
      $union= '';
      foreach ($t->getTypes() as $component) {
        $name= $component->getName();
        $union.= '|'.($map[$name] ?? strtr($name, '\\', '.'));
      }
      return substr($union, 1);
    } else if ('array' === ($name= PHP_VERSION_ID >= 70100 ? $t->getName() : $t->__toString())) {
      // Check for more specific type, e.g. `string[]` in api documentation
    } else {
      return $map[$name] ?? strtr($name, '\\', '.');
    }

    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1]);
    $r= $details[DETAIL_ARGUMENTS][$this->_details[2]] ?? null;
    return null === $r ? $name : rtrim(ltrim($r, '&'), '.');
  }

  /**
   * Get parameter's type restriction.
   *
   * @return  lang.Type or NULL if there is no restriction
   * @throws  lang.ClassFormatException if the restriction cannot be resolved
   */
  public function getTypeRestriction() {
    $t= $this->_reflect->getType();
    if (null === $t) return null;

    try {
      if ($t instanceof \ReflectionUnionType) {
        $union= [];
        foreach ($t->getTypes() as $component) {
          $union[]= $this->resolve($component->getName());
        }
        return new TypeUnion($union);
      } else {
        return $this->resolve(PHP_VERSION_ID >= 70100 ? $t->getName() : $t->__toString());
      }
    } catch (ClassLoadingException $e) {
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
    return $this->_reflect->isOptional();
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
    if ($this->_reflect->isOptional()) {
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
    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1]);
    $r= $details[DETAIL_TARGET_ANNO]['$'.$this->_reflect->getName()] ?? [];

    return $key ? array_key_exists($key, $r[$name] ?? []) : array_key_exists($name, $r);
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
    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1]);
    $r= $details[DETAIL_TARGET_ANNO]['$'.$this->_reflect->getName()] ?? [];

    if ($key) {
      if (array_key_exists($key, $r[$name] ?? [])) return $r[$name][$key];
    } else {
      if (array_key_exists($name, $r)) return $r[$name];
    }

    throw new ElementNotFoundException('Annotation "'.$name.($key ? '.'.$key : '').'" does not exist');
  }

  /**
   * Retrieve whether a method has annotations
   *
   * @return  bool
   */
  public function hasAnnotations() {
    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1]);
    return !empty($details[DETAIL_TARGET_ANNO]['$'.$this->_reflect->getName()] ?? []);
  }

  /**
   * Retrieve all of a method's annotations
   *
   * @return  var[] annotations
   */
  public function getAnnotations() {
    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1]);
    return $details[DETAIL_TARGET_ANNO]['$'.$this->_reflect->getName()] ?? [];
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
      $this->_reflect->isOptional() ? '= '.Objects::stringOf($this->_reflect->getDefaultValue()) : ''
    );
  }
}
