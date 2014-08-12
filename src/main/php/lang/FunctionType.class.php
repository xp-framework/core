<?php namespace lang;

/**
 * Represents wildcard types
 *
 * @see   xp://lang.Type
 * @test  xp://net.xp_framework.unittest.core.FunctionTypeTest
 */
class FunctionType extends Type {
  protected $signature;
  protected $returnType;

  /**
   * Creates a new array type instance
   *
   * @param  lang.Type[] $signature
   * @param  lang.Type $returnType
   */
  public function __construct(array $signature, $returnType) {
    $this->signature= $signature;
    $this->returnType= $returnType;
    parent::__construct(sprintf(
      'function(%s): %s',
      implode(',', array_map(function($e) { return $e->getName(); }, $signature)),
      $this->returnType->getName()
    ), null);
  }

  /** @return lang.Type[] */
  public function signature() {
    return $this->signature;
  }

  /** @return lang.Type */
  public function returnType() {
    return $this->returnType;
  }

  /**
   * Get a type instance for a given name
   *
   * @param   string name
   * @return  lang.ArrayType
   * @throws  lang.IllegalArgumentException if the given name does not correspond to a function type
   */
  public static function forName($name) {
    if (0 !== strncmp($name, 'function(', 9)) {
      throw new IllegalArgumentException('Not a function type: '.$name);
    }

    $signature= [];
    if (')' === $name{9}) {
      $args= substr($name, 10);
      $o= strpos($args, ':');
    } else for ($args= substr($name, 8), $o= 0, $brackets= 0, $i= 0, $s= strlen($args); $i < $s; $i++) {
      if (':' === $args{$i} && 0 === $brackets) {
        $signature[]= parent::forName(substr($args, $o + 1, $i- $o- 2));
        $o= $i+ 1;
        break;
      } else if (',' === $args{$i} && 1 === $brackets) {
        $signature[]= parent::forName(substr($args, $o + 1, $i- $o- 1));
        $o= $i+ 1;
      } else if ('(' === $args{$i}) {
        $brackets++;
      } else if (')' === $args{$i}) {
        $brackets--;
      }
    }

    return new self($signature, Type::forName(ltrim(substr($args, $o+ 1), ' ')));
  }

  /**
   * Returns type literal
   *
   * @return  string
   */
  public function literal() {
    throw new IllegalStateException('Function types cannot be used in type literals');
  }

  /**
   * Determines whether the specified object is an instance of this
   * type. 
   *
   * @param   var $obj
   * @return  bool
   */
  public function isInstance($obj) {
    if ($obj instanceof \Closure) {
      $r= new \ReflectionFunction($obj);
      $params= $r->getParameters();
      if (sizeof($params) !== sizeof($this->signature)) return false;
      foreach ($this->signature as $i => $type) {
        if ($params[$i]->isArray()) {
          if (!$type instanceof ArrayType && !$type instanceof MapType) return false;
        } else if ($params[$i]->isCallable()) {
          if (!$type instanceof FunctionType) return false;
        } else if (null === ($class= $params[$i]->getClass())) {
          if (!$type->equals(Type::$VAR)) return false;
        } else {
          if (!$type->isAssignableFrom(new XPClass($class))) return false;
        }
      }
      return true;
    }
    return false;
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var value
   * @return  var
   */
  public function newInstance($value= null) {
    // TBI
  }

  /**
   * Cast a value to this type
   *
   * @param   var value
   * @return  var
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    // TBI
  }

  /**
   * Tests whether this type is assignable from another type
   *
   * @param   var $type
   * @return  bool
   */
  public function isAssignableFrom($type) {
    // TBI
  }
}
