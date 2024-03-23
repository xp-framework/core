<?php namespace lang\reflect;

use lang\{
  ClassLoader,
  ElementNotFoundException,
  IllegalAccessException,
  IllegalArgumentException,
  Type,
  TypeUnion,
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

  /** Gets field type */
  public function getType(): Type {
    $api= function() {
      $details= XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
      $r= $details[DETAIL_RETURNS] ?? $details[DETAIL_ANNOTATIONS]['type'] ?? null;
      return $r ? ltrim($r, '&') : null;
    };

    return Type::resolve($this->_reflect->getType(), $this->resolve(), $api) ?? Type::$VAR;
  }

  /** Gets field type's name */
  public function getTypeName(): string {
    static $map= [
      'mixed'   => 'var',
      'false'   => 'bool',
      'boolean' => 'bool',
      'double'  => 'float',
      'integer' => 'int',
    ];

    if (null === ($t= $this->_reflect->getType())) {

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
      return substr($intersection, 1);
    } else {
      $name= $t->getName();

      // Check array for more specific types, e.g. `string[]` in api documentation
      if ('array' !== $name) {
        return $map[$name] ?? strtr($name, '\\', '.');
      }
    }

    $details= XPClass::detailsForField($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    $f= $details[DETAIL_RETURNS] ?? $details[DETAIL_ANNOTATIONS]['type'] ?? null;
    return null === $f ? $name : rtrim(ltrim($f, '&'), '.');
  }

  /**
   * Get field's type restriction.
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
      throw new IllegalArgumentException(sprintf(
        'Passed argument is not a %s class (%s)',
        XPClass::nameOf($this->_class),
        typeof($instance)->getName()
      ));
    }

    // Check modifiers. If scope is an instance of this class, allow
    // protected method invocation (which the PHP reflection API does 
    // not).
    $m= $this->_reflect->getModifiers();
    $public= $m & MODIFIER_PUBLIC;
    if (!$public && !$this->accessible) {
      $t= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
      $scope= $t[1]['class'] ?? ClassLoader::getDefault()->loadUri($t[0]['file'])->literal();
      $decl= $this->_reflect->getDeclaringClass()->getName();
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
          $this->_reflect->getName(),
          $scope
        ));
      }
    }

    try {
      $public || $this->_reflect->setAccessible(true);
      return $this->_reflect->getValue($instance);
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
      throw new IllegalArgumentException(sprintf(
        'Passed argument is not a %s class (%s)',
        XPClass::nameOf($this->_class),
        typeof($instance)->getName()
      ));
    }
  
    // Check modifiers. If scope is an instance of this class, allow
    // protected method invocation (which the PHP reflection API does 
    // not).
    $m= $this->_reflect->getModifiers();
    $public= $m & MODIFIER_PUBLIC;
    if (!$public && !$this->accessible) {
      $t= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
      $scope= $t[1]['class'] ?? ClassLoader::getDefault()->loadUri($t[0]['file'])->literal();
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
      $public || $this->_reflect->setAccessible(true);
      return $this->_reflect->setValue($instance, $value);
    } catch (Throwable $e) {
      throw $e;
    } catch (\Error $e) {
      throw new IllegalAccessException($e->getMessage(), $e); // PHP 8.1 raises errors when modifying readonly properties
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
