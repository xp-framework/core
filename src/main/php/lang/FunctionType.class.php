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
   * @return  self
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
   * Verifies a reflection function or method
   *
   * @param  php.ReflectionFunctionAbstract $value
   * @param  function(string): var $value A function to invoke when verification fails
   * @param  php.ReflectionClass $class Class to get details from, optionally
   * @return var
   */
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

  /**
   * Returns a verified function instance for a given value. Supports the following:
   *
   * - A closure
   * - A string referencing a function, e.g. 'strlen' or 'typeof'
   * - A string referencing an instance creation expression: 'lang.Object::new'
   * - A string referencing a static method: 'lang.XPClass::forName'
   * - An array of two strings referencing a static method: ['lang.XPClass', 'forName']
   * - An array of an instance and a string referencing an instance method: [$this, 'getName']
   *
   * @param  var $arg
   * @param  function(string): var $value A function to return when verification fails
   * @param  bool $return
   * @return php.Closure
   */
  protected function verified($arg, $false, $return= true) {
    $result= false;
    if ($arg instanceof \Closure) {
      if ($this->verify(new \ReflectionFunction($arg), $false)) {
        $result= $return ? $arg : true;
      }
    } else if (is_string($arg)) {
      $r= sscanf($arg, '%[^:]::%s', $class, $method);
      if (2 === $r) {
        $result= $this->closureOf($class, $method, $false, $return);
      } else if (function_exists($arg)) {
        $r= new \ReflectionFunction($arg);
        if ($this->verify($r, $false)) {
          $result= $return ? $r->getClosure() : true;
        }
      } else {
        return $false('Function "'.$arg.'" does not exist');
      }
    } else if (is_array($arg) && 2 === sizeof($arg)) {
      $result= $this->closureOf($arg[0], $arg[1], $false, $return);
    } else {
      return $false('Unsupported type');
    }
    return $result;
  }

  protected function closureOf($arg, $method, $throw, $return) {
    if ('new' === $method) {
      $class= \xp::reflect($arg);
      if (method_exists($class, '__construct')) {
        $r= new \ReflectionMethod($class, '__construct');
        if (!$this->verify($r, $throw, $r->getDeclaringClass())) return false;
      } else {
        if (!$this->returns->isAssignableFrom(XPClass::forName(\xp::nameOf($class)))) return $throw('Class type mismatch');
      }
      if ($return) {
        $c= new \ReflectionClass($class);
        $result= function() use($c) { return $c->newInstanceArgs(func_get_args()); };
      } else {
        $result= true;
      }
    } else if (is_string($arg)) {
      $class= \xp::reflect($arg);
      if (!method_exists($class, $method)) return $throw('Method '.\xp::nameOf($class).'::'.$method.' does not exist');
      $r= new \ReflectionMethod($class, $method);
      if (!$r->isStatic()) return $throw('Method '.\xp::nameOf($class).'::'.$method.' referenced as static method but not static');
      if (!$this->verify($r, $throw, $r->getDeclaringClass())) return false;
      $result= $return ? $r->getClosure(null) : true;
    } else {
      if (!method_exists($arg, $method)) return $throw('Method '.\xp::nameOf(get_class($arg)).'::'.$method.' does not exist');
      $r= new \ReflectionMethod($arg, $method);
      if (!$this->verify($r, $throw, $r->getDeclaringClass())) return false;
      $result= $return ? $r->getClosure($arg) : true;
    }
    return $result;
  }

  /**
   * Determines whether the specified object is an instance of this
   * type. 
   *
   * @param   var $obj
   * @return  bool
   */
  public function isInstance($obj) {
    return $this->verified($obj, function($m) { return false; }, false);
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var value
   * @return  var
   */
  public function newInstance($value= null) {
    return $this->verified($value, function($m) use($value) { raise('lang.IllegalArgumentException', sprintf(
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
    return null === $value ? null : $this->verified($value, function($m) use($value) { raise('lang.ClassCastException', sprintf(
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

  /**
   * Invokes a given function with the given arguments.
   *
   * @param   var $func
   * @return  var
   * @throws  lang.IllegalArgumentException in case the passed function is not an instance of this type
   * @throws  lang.reflect.TargetInvocationException for any exception raised from the invoked function
   */
  public function invoke($func, $args= []) {
    $closure= $this->verified($func, function($m) use($func) { raise('lang.IllegalArgumentException', sprintf(
      'Passed argument is not of a %s type (%s): %s',
      $this->getName(),
      \xp::typeOf($func),
      $m
    )); });
    try {
      return call_user_func_array($closure, $args);
    } catch (SystemExit $e) {
      throw $e;
    } catch (Throwable $e) {
      throw new \lang\reflect\TargetInvocationException($this->getName(), $e);
    }
  }
}
