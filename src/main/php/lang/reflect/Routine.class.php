<?php namespace lang\reflect;

use lang\{ElementNotFoundException, XPClass, Value, Type, TypeUnion};
use util\Objects;

/**
 * Base class for methods and constructors. Note that the methods provided
 * in this class (except for getName()) are implemented using a tokenizer
 * on the class files, gathering its information from the API docs.
 *
 * This, of course, will not be as fast as if the details were provided by
 * PHP itself and will also rely on the API docs being consistent and 
 * correct.
 *
 * @see   lang.reflect.Method
 * @see   lang.reflect.Constructor
 * @see   https://php.net/manual/en/reflectionmethod.setaccessible.php
 * @test  lang.unittest.ReflectionTest
 */
class Routine implements Value {
  protected $accessible= false;
  protected $_class= null;
  public $_reflect= null;

  /**
   * Constructor
   *
   * @param  string $class
   * @param  php.ReflectionMethod $reflect
   */    
  public function __construct($class, $reflect) {
    $this->_class= $class;
    $this->_reflect= $reflect;
  }
  
  /** Get routine's name */
  public function getName(): string { return $this->_reflect->getName(); }
  
  /** Retrieve this method's modifiers */    
  public function getModifiers(): int {
  
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
   * @return lang.reflect.Parameter[]
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
   * @param  int $offset
   * @return lang.reflect.Parameter or NULL if it does not exist
   */
  public function getParameter($offset) {
    $list= $this->_reflect->getParameters();
    return isset($list[$offset]) 
      ? new Parameter($list[$offset], [$this->_reflect->getDeclaringClass()->getName(), $this->_reflect->getName(), $offset])
      : null
    ;
  }
  
  /** Retrieve how many parameters this method declares (including optional ones) */
  public function numParameters(): int {
    return $this->_reflect->getNumberOfParameters();
  }

  /**
   * Resolution resolve handling `static`, `self` and `parent`.
   *
   * @return [:(function(string): lang.Type)]
   */
  private function resolve() {
    return [
      'static' => fn() => new XPClass($this->_class),
      'self'   => fn() => new XPClass($this->_reflect->getDeclaringClass()),
      'parent' => fn() => new XPClass($this->_reflect->getDeclaringClass()->getParentClass()),
    ];
  }

  /**
   * Get return type.
   *
   * @return  lang.Type
   * @throws  lang.ClassFormatException if the restriction cannot be resolved
   */
  public function getReturnType(): Type {
    $api= function() {
      $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
      $r= $details[DETAIL_RETURNS] ?? null;
      return $r ? ltrim($r, '&') : null;
    };

    // Check "self" (and declaring class, see https://github.com/php/php-src/issues/18373)
    $decl= $this->_reflect->getDeclaringClass();
    $type= $this->_reflect->getReturnType();
    if ($type instanceof \ReflectionNamedType && (PHP_VERSION_ID >= 80500 ? $decl->getName() : 'self') === $type->getName()) {
      return ($specific= $api()) ? Type::named($specific, $this->resolve()) : new XPClass($decl);
    }

    return Type::resolve($type, $this->resolve(), $api) ?? Type::$VAR;
  }

  /** Retrieve return type name */
  public function getReturnTypeName(): string {
    static $map= [
      'mixed'   => 'var',
      'false'   => 'bool',
      'boolean' => 'bool',
      'double'  => 'float',
      'integer' => 'int',
    ];

    $t= $this->_reflect->getReturnType();
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

      // Check array, self, void and callable for more specific types, e.g. `string[]`,
      // `static`, `never` or `function(): string` in api documentation
      if ('array' !== $name && 'callable' !== $name && 'self' !== $name && 'void' !== $name) {
        return $nullable.($map[$name] ?? strtr($name, '\\', '.'));
      }
    }

    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    $r= $details[DETAIL_RETURNS] ?? null;
    return null === $r ? $nullable.$name : rtrim(ltrim($r, '&'), '.');
  }

  /**
   * Get return type restriction.
   *
   * @return  lang.Type or NULL if there is no restriction
   * @throws  lang.ClassFormatException if the restriction cannot be resolved
   */
  public function getReturnTypeRestriction() {
    try {
      return Type::resolve($this->_reflect->getReturnType(), $this->resolve());
    } catch (ClassLoadingException $e) {
      throw new ClassFormatException(sprintf(
        'Typehint for %s::%s()\'s return type cannot be resolved: %s',
        strtr($this->_class, '\\', '.'),
        $this->_reflect->getName(),
        $e->getMessage()
      ));
    }
  }

  /**
   * Retrieve exception names
   *
   * @return string[]
   */
  public function getExceptionNames() {
    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    return $details ? $details[DETAIL_THROWS] : [];
  }

  /**
   * Retrieve exception types
   *
   * @return lang.XPClass[]
   */
  public function getExceptionTypes() {
    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    if (!$details) return [];

    $thrown= [];
    foreach ($details[DETAIL_THROWS] as $name) {
      $thrown[]= XPClass::forName($name);
    }
    return $thrown;
  }
  
  /**
   * Returns the XPClass object representing the class or interface 
   * that declares the method represented by this Method object.
   *
   * @return lang.XPClass
   */
  public function getDeclaringClass() {
    return new XPClass($this->_reflect->getDeclaringClass());
  }
  
  /**
   * Retrieves the api doc comment for this method. Returns NULL if
   * no documentation is present.
   *
   * @return string
   */
  public function getComment() {
    if (!($details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName()))) return null;
    return $details[DETAIL_COMMENT];
  }

  /**
   * Check whether an annotation exists
   *
   * @param  string $name
   * @param  string $key default NULL
   * @return bool
   */
  public function hasAnnotation($name, $key= null): bool {
    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
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
   * @param  string $name
   * @param  string $key default NULL
   * @return var
   * @throws lang.ElementNotFoundException
   */
  public function getAnnotation($name, $key= null) {
    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    if ($key) {
      $a= $details[DETAIL_ANNOTATIONS][$name] ?? null;
      if (is_array($a) && array_key_exists($key, $a)) return $a[$key];
    } else {
      if (array_key_exists($name, $details[DETAIL_ANNOTATIONS] ?? [])) return $details[DETAIL_ANNOTATIONS][$name];
    }

    throw new ElementNotFoundException('Annotation "'.$name.($key ? '.'.$key : '').'" does not exist');
  }

  /** Retrieve whether a method has annotations */
  public function hasAnnotations(): bool {
    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    return $details ? !empty($details[DETAIL_ANNOTATIONS]) : false;
  }

  /**
   * Retrieve all of a method's annotations
   *
   * @return [:var] annotations
   */
  public function getAnnotations() {
    $details= XPClass::detailsForMethod($this->_reflect->getDeclaringClass(), $this->_reflect->getName());
    return $details ? $details[DETAIL_ANNOTATIONS] : [];
  }
  
  /** 
   * Sets whether this routine should be accessible from anywhere, 
   * regardless of its visibility level.
   */
  public function setAccessible(bool $flag): self {
    $this->accessible= $flag;
    return $this;
  }
  
  /** Compares this routine to another value */
  public function compareTo($value): int {
    if (!($value instanceof self)) return 1;
    if (0 !== ($c= $value->_reflect->getName() <=> $this->_reflect->getName())) return $c;
    if (0 !== ($c= $value->getDeclaringClass()->compareTo($this->getDeclaringClass()))) return $c;
    return 0;
  }

  /** Returns a hashcode for this routine */
  public function hashCode(): string {
    return 'R['.$this->_reflect->getDeclaringClass().$this->_reflect->getName();
  }
  
  /** Retrieve string representation */
  public function toString(): string {
    $signature= '';
    foreach ($this->getParameters() as $param) {
      if ($param->isOptional()) {
        $signature.= ', ['.$param->getTypeName().' $'.$param->getName().'= '.str_replace("\n", ' ', Objects::stringOf($param->getDefaultValue())).']';
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
