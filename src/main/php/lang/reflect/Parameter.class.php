<?php namespace lang\reflect;

use lang\{ElementNotFoundException, IllegalStateException, ClassLoadingException, ClassNotFoundException, XPClass, Type, TypeUnion};
use util\Objects;

/**
 * Represents a method's parameter
 *
 * @see   lang.reflect.Method#getParameter
 * @see   lang.reflect.Method#getParameters
 * @see   lang.reflect.Method#numParameters
 * @test  lang.unittest.MethodParametersTest
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
   * Resolution resolve handling `self` and `parent` (`static` is only for return
   * types, see https://wiki.php.net/rfc/static_return_type#allowed_positions).
   *
   * @return [:(function(string): lang.Type)]
   */
  private function resolve() {
    return [
      'self'   => fn() => new XPClass($this->_reflect->getDeclaringClass()),
      'parent' => fn() => new XPClass($this->_reflect->getDeclaringClass()->getParentClass()),
    ];
  }

  /**
   * Get parameter's type.
   *
   * @return lang.Type
   * @throws lang.ClassFormatException if the restriction cannot be resolved
   */
  public function getType() {
    $api= function() {
      $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1]);
      $r= $details[DETAIL_ARGUMENTS][$this->_details[2]] ?? null;
      return $r ? rtrim(ltrim($r, '&'), '.') : null;
    };
    return Type::resolve($this->_reflect->getType(), $this->resolve(), $api) ?? Type::$VAR;
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
      $nullable= '';

      // Check for type in api documentation
      $name= 'var';
    } else if ($t instanceof \ReflectionUnionType) {
      $union= '';
      $nullable= '';
      foreach ($t->getTypes() as $component) {
        if ('null' === ($name= $component->getName())) {
          $nullable= '?';
        } else {
          $union.= '|'.($map[$name] ?? strtr($name, '\\', '.'));
        }
      }
      return $nullable.substr($union, 1);
    } else if ($t instanceof \ReflectionIntersectionType) {
      $intersection= '';
      foreach ($t->getTypes() as $component) {
        $name= $component->getName();
        $intersection.= '&'.($map[$name] ?? strtr($name, '\\', '.'));
      }
      return ($t->allowsNull() ? '?' : '').substr($intersection, 1);
    } else {
      $nullable= $t->allowsNull() ? '?' : '';
      $name= $t->getName();

      // Check array and callable for more specific types, e.g. `string[]` or
      // `function(): string` in api documentation
      if ('array' !== $name && 'callable' !== $name) {
        return $nullable.($map[$name] ?? strtr($name, '\\', '.'));
      }
    }

    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1]);
    $r= $details[DETAIL_ARGUMENTS][$this->_details[2]] ?? null;
    return null === $r ? $nullable.$name : rtrim(ltrim($r, '&'), '.');
  }

  /**
   * Get parameter's type restriction.
   *
   * @return  lang.Type or NULL if there is no restriction
   * @throws  lang.ClassNotFoundException if the restriction cannot be resolved
   */
  public function getTypeRestriction() {
    try {
      return Type::resolve($this->_reflect->getType(), $this->resolve());
    } catch (ClassLoadingException $e) {
      throw new ClassNotFoundException(sprintf(
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
   * Get default value. Additionally checks `default` annotation for NULL defaults.
   *
   * @throws  lang.IllegalStateException in case this argument is not optional
   * @return  var
   */
  public function getDefaultValue() {
    if ($this->_reflect->isOptional()) {
      if (!$this->_reflect->isDefaultValueAvailable()) return null;

      $value= $this->_reflect->getDefaultValue();
      if (null === $value && ($details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_details[1]))) {
        return $details[DETAIL_TARGET_ANNO]['$'.$this->_reflect->getName()]['default'] ?? null;
      }
      return $value;
    }

    throw new IllegalStateException('Parameter "'.$this->_reflect->getName().'" has no default value');
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
