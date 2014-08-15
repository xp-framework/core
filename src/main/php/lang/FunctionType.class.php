<?php namespace lang;

/**
 * Represents function types
 *
 * @see   xp://lang.Type
 * @test  xp://net.xp_framework.unittest.core.FunctionTypeTest
 */
class FunctionType extends Type {
  protected $signature;
  protected $returns;

  /**
   * Creates a new array type instance
   *
   * @param  lang.Type[] $signature
   * @param  lang.Type $returns
   */
  public function __construct(array $signature, $returns) {
    $this->signature= $signature;
    $this->returns= $returns;
    parent::__construct(sprintf(
      'function(%s): %s',
      implode(',', array_map(function($e) { return $e->getName(); }, $signature)),
      $this->returns->getName()
    ), null);
  }

  /** @return lang.Type[] */
  public function signature() {
    return $this->signature;
  }

  /** @return lang.Type */
  public function returns() {
    return $this->returns;
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

  protected function verify($r, $false, $class= null) {
    if (sizeof($this->signature) < $r->getNumberOfRequiredParameters()) {
      return $false('Required signature length mismatch, expecting '.sizeof($this->signature).', have '.$r->getNumberOfParameters());
    }

    $details= $class ? XPClass::detailsForMethod($class->getName(), $r->getName()) : null;
    if (isset($details[DETAIL_RETURNS])) {
      $returns= Type::forName($details[DETAIL_RETURNS]);
      if (!$this->returns->isAssignableFrom($returns)) {
        return $false('Return type mismatch, expecting '.$this->returns->getName().', have '.$returns->getName()); 
      }
    }

    $params= $r->getParameters();
    foreach ($this->signature as $i => $type) {
      if (!isset($params[$i])) return $false('No parameter #'.($i + 1));
      if (isset($details[DETAIL_ARGUMENTS][$i])) {
        $param= Type::forName($details[DETAIL_ARGUMENTS][$i]);
        if (!$type->isAssignableFrom($param)) {
          return $false('Parameter #'.($i + 1).' not a '.$param->getName().' type: '.$type->getName());
        }
      } else {
        $param= $params[$i];
        if ($param->isArray()) {
          if (!$type->equals(Primitive::$ARRAY) && !$type instanceof ArrayType && !$type instanceof MapType) {
            return $false('Parameter #'.($i + 1).' not an array type: '.$type->getName());
          }
        } else if ($param->isCallable()) {
          if (!$type instanceof FunctionType) {
            return $false('Parameter #'.($i + 1).' not a function type: '.$type->getName());
          }
        } else if (null !== ($class= $param->getClass())) {
          if (!$type->isAssignableFrom(new XPClass($class))) {
            return $false('Parameter #'.($i + 1).' not a '.$class->getName().': '.$type->getName());
          }
        }
      }
    }
    return true;
  }

  /**;
   * Determines whether the specified object is an instance of this
   * type. 
   *
   * @param   var $obj
   * @return  bool
   */
  public function isInstance($obj) {
    $false= function($m) { return false; };
    if ($obj instanceof \Closure) {
      return $this->verify(new \ReflectionFunction($obj), $false);
    } else if (is_string($obj) && function_exists($obj)) {
      return $this->verify(new \ReflectionFunction($obj), $false);
    } else if (is_array($obj) && 2 === sizeof($obj)) {
      if (is_string($obj[0]) && method_exists($class= \xp::reflect($obj[0]), $obj[1])) {
        $r= new \ReflectionMethod($class, $obj[1]);
        return $this->verify($r, $false, $r->getDeclaringClass());
      } else if (method_exists($obj[0], $obj[1])) {
        $r= new \ReflectionMethod($obj[0], $obj[1]);
        return $this->verify($r, $false, $r->getDeclaringClass());
      }
    }
    return false;
  }

  protected function instance($value, $throw) {
    if ($value instanceof \Closure) {
      $this->verify(new \ReflectionFunction($value), $throw);
      return $value;
    } else if (is_string($value)) {
      if (!function_exists($value)) $throw('Function '.$value.' does not exist');
      $r= new \ReflectionFunction($value);
      $this->verify($r, $throw);
      return $r->getClosure();
    } else if (is_array($value) && 2 === sizeof($value)) {
      if (is_string($value[0])) {
        $class= \xp::reflect($value[0]);
        if (!method_exists($class, $value[1])) $throw('Method '.$class.'::'.$value[1].' does not exist');
        $r= new \ReflectionMethod($class, $value[1]);
        $this->verify($r, $throw, $r->getDeclaringClass());
        return $r->getClosure(null);
      } else {
        if (!method_exists($value[0], $value[1])) $throw('Method '.\xp::nameOf(get_class($value[0])).'::'.$value[1].' does not exist');
        $r= new \ReflectionMethod($value[0], $value[1]);
        $this->verify($r, $throw, $r->getDeclaringClass());
        return $r->getClosure($value[0]);
      }
    } else {
      $throw('Unsupported type');
    }
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var value
   * @return  var
   */
  public function newInstance($value= null) {
    return $this->instance($value, function($m) use($value) { raise('lang.IllegalArgumentException', sprintf(
      'Cannot create instances of the %s type from %s: %s',
      $this->getName(),
      \xp::typeOf($value),
      $m
    )); });
  }

  /**
   * Cast a value to this type
   *
   * @param   var value
   * @return  var
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    return null === $value ? null : $this->instance($value, function($m) use($value) { raise('lang.ClassCastException', sprintf(
      'Cannot cast %s to the %s type: %s',
      \xp::typeOf($value),
      $this->getName(),
      $m
    )); });
  }

  /**
   * Tests whether this type is assignable from another type
   *
   * @param   var $type
   * @return  bool
   */
  public function isAssignableFrom($type) {
    $t= $type instanceof Type ? $type : Type::forName($type);
    if (!($t instanceof self) || sizeof($t->signature) !== sizeof($this->signature)) return false;
    foreach ($this->signature as $i => $type) {
      if (!$type->isAssignableFrom($t->signature[$i])) return false;
    }
    return $this->returns->isAssignableFrom($t->returns);
  }
}
