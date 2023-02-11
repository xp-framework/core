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

  static function __static() { }

  /**
   * Creates a new array type instance
   *
   * @param  string[]|lang.Type[] $signature
   * @param  string|lang.Type $returns
   */
  public function __construct(array $signature= null, $returns) {
    if (null === $signature) {
      $this->signature= null;
      $name= ',?';
    } else {
      $this->signature= [];
      $name= '';
      foreach ($signature as $type) {
        if ($type instanceof Type) {
          $this->signature[]= $type;
          $name.= ','.$type->getName();
        } else {
          $this->signature[]= Type::forName($type);
          $name.= ','.$type;
        }
      }
    }
    $this->returns= $returns instanceof Type ? $returns : Type::forName($returns);
    parent::__construct(sprintf('(function(%s): %s)', substr($name, 1), $this->returns->getName()), null);
  }

  /** @return lang.Type[] */
  public function signature() { return $this->signature; }

  /** Return type */
  public function returns(): Type { return $this->returns; }

  /**
   * Get a type instance for a given name
   *
   * @param   string name
   * @return  self
   * @throws  lang.IllegalArgumentException if the given name does not correspond to a function type
   */
  public static function forName($name) {
    $t= parent::forName($name);
    if ($t instanceof self) return $t;

    throw new IllegalArgumentException($name.' is not a function type');
  }

  /** Returns type literal */
  public function literal(): string {
    return sprintf(
      "function\x9a%s\x9b\x95%s",
      null === $this->signature ? "\xbf" : implode("\xb8", array_map(function($e) { return $e->literal(); }, $this->signature)),
      $this->returns->literal()
    );
  }

  /**
   * Verifies a reflection function or method
   *
   * @param  php.ReflectionFunctionAbstract $value
   * @param  [lang.Type] $signature
   * @param  function(string): var $value A function to invoke when verification fails
   * @param  php.ReflectionClass $class Class to get details from, optionally
   * @return var
   */
  protected function verify($r, $signature, $false, $class= null) {
    if ($class) {
      $details= XPClass::detailsForMethod($class, $r->getName());
      $resolve= [
        'static' => function() use($class) { return new XPClass($class); },
        'self'   => function() use($class) { return new XPClass($class); },
        'parent' => function() use($class) { return new XPClass($class->getParentClass()); },
      ];
    } else {
      $details= null;
      $resolve= [];
    }

    // Verify return type
    $returns= Type::resolve($r->getReturnType(), $resolve) ?? (isset($details[DETAIL_RETURNS])
      ? Type::forName($details[DETAIL_RETURNS])
      : null
    );
    if ($returns && !$this->returns->equals($returns) && !$this->returns->isAssignableFrom($returns)) {
      return $false('Return type mismatch, expecting '.$this->returns->getName().', have '.$returns->getName());
    }

    if (null === $signature) return true;
    $params= $r->getParameters();
    $i= -1;

    // Verify signature
    foreach ($signature as $i => $type) {
      if (isset($details[DETAIL_ARGUMENTS][$i])) {
        if (0 === substr_compare($details[DETAIL_ARGUMENTS][$i], '...', -3)) return true;  // No further checks necessary

        $param= Type::forName($details[DETAIL_ARGUMENTS][$i]);
        if (!$type->isAssignableFrom($param)) {
          return $false('Parameter #'.($i + 1).' not a '.$param->getName().' type: '.$type->getName());
        }
      } else if (!isset($params[$i])) {
        return $false('No parameter #'.($i + 1));
      } else {
        if ($params[$i]->isVariadic()) return true;  // No further checks necessary

        $param= Type::resolve($params[$i]->getType(), $resolve);
        if (null === $param) continue;

        if (!$type->isAssignableFrom($param)) {
          return $false('Parameter #'.($i + 1).' not a '.$param->getName().' type: '.$type->getName());
        }
      }
    }

    // Check if there are required parameters
    while (++$i < $r->getNumberOfParameters()) {
      $param= $params[$i];
      if ($param->isOptional() || $param->isVariadic()) {
        return true;  // No further checks necessary
      } else {
        return $false('Signature mismatch, additional required parameter $'.$param->getName().' found');
      }
    }
    return true;
  }

  /**
   * Returns a verified function instance for a given value. Supports the following:
   *
   * - A closure
   * - A string referencing a function, e.g. 'strlen' or 'typeof'
   * - A string referencing an instance creation expression: 'util.Date::new'
   * - A string referencing a static method: 'lang.XPClass::forName'
   * - An array of two strings referencing a static method: ['lang.XPClass', 'forName']
   * - An array of an instance and a string referencing an instance method: [$this, 'getName']
   *
   * @param  var $arg
   * @param  function(string): var $false A function to return when verification fails
   * @param  bool $return Whether to return the closure, or TRUE
   * @return php.Closure
   */
  protected function verified($arg, $false, $return= true) {
    if ($arg instanceof \Closure) {
      if ($this->verify(new \ReflectionFunction($arg), $this->signature, $false)) {
        return $return ? $arg : true;
      }
    } else if (is_string($arg)) {
      $r= sscanf($arg, '%[^:]::%s', $class, $method);
      if (2 === $r) {
        return $this->verifiedMethod($class, $method, $false, $return);
      } else if (function_exists($arg)) {
        $r= new \ReflectionFunction($arg);
        if ($this->verify($r, $this->signature, $false)) {
          return $return ? $r->getClosure() : true;
        }
      } else {
        return $false('Function "'.$arg.'" does not exist');
      }
    } else if (is_array($arg) && 2 === sizeof($arg)) {
      return $this->verifiedMethod($arg[0], $arg[1], $false, $return);
    } else if (is_object($arg) && method_exists($arg, '__invoke')) {
      $inv= new \ReflectionMethod($arg, '__invoke');
      if ($this->verify($inv, $this->signature, $false)) {
        return $return ? $inv->getClosure($arg) : true;
      }
    } else {
      return $false('Unsupported type');
    }

    return $false('Verification failed');
  }

  /**
   * Returns a verified function instance for a given arg and method.
   *
   * @param  var $arg Either a string referencing a class or an object
   * @param  string $method
   * @param  function(string): var $false A function to return when verification fails
   * @param  bool $return Whether to return the closure, or TRUE
   * @return php.Closure
   */
  protected function verifiedMethod($arg, $method, $false, $return) {
    if ('new' === $method) {
      $class= literal($arg);
      if (method_exists($class, '__construct')) {
        $r= new \ReflectionMethod($class, '__construct');
        if (!$this->verify($r, $this->signature, $false, $r->getDeclaringClass())) return false;
      } else {
        if (!$this->returns->isAssignableFrom(new XPClass($class))) return $false('Class type mismatch');
      }
      $c= new \ReflectionClass($class);
      if (!$c->isInstantiable()) return $false($arg.' cannot be instantiated');
      return $return ? function(... $args) use($c) { return $c->newInstanceArgs($args); } : true;
    } else if (is_string($arg) && is_string($method)) {
      $class= literal($arg);
      if (!method_exists($class, $method)) return $false('Method '.$arg.'::'.$method.' does not exist');
      $r= new \ReflectionMethod($class, $method);
      if ($r->isStatic()) {
        if ($this->verify($r, $this->signature, $false, $r->getDeclaringClass())) {
          return $return ? $r->getClosure(null) : true;
        }
      } else {
        if (null === $this->signature) {
          $verify= null;
        } else {
          if (empty($this->signature) || !$this->signature[0]->isAssignableFrom(new XPClass($class))) {
            return $false('Method '.$arg.'::'.$method.' requires instance of class as first parameter');
          }
          $verify= array_slice($this->signature, 1);
        }
        if ($this->verify($r, $verify, $false, $r->getDeclaringClass())) {
          return $return ? function(... $args) use($r) {
            $self= array_shift($args);
            try {
              return $r->invokeArgs($self, $args);
            } catch (\ReflectionException $e) {
              throw new IllegalArgumentException($e->getMessage());
            }
          } : true;
        }
      }
    } else if (is_object($arg) && is_string($method)) {
      if (!method_exists($arg, $method)) return $false('Method '.nameof($arg).'::'.$method.' does not exist');
      $r= new \ReflectionMethod($arg, $method);
      if ($this->verify($r, $this->signature, $false, $r->getDeclaringClass())) {
        return $return ? $r->getClosure($arg) : true;
      }
    } else {
      return $false('Array argument must either be [string, string] or an [object, string]');
    }

    return $false('Verifying method failed');
  }

  /** Determines whether the specified object is an instance of this type */
  public function isInstance($obj): bool {
    return $this->verified($obj, function($m) { return false; }, false);
  }

  /**
   * Returns a new instance of this object
   *
   * @param   var... $args
   * @return  var
   */
  public function newInstance(... $args) {
    $value= $args[0] ?? null;
    return $this->verified($value, function($m) use($value) { throw new IllegalArgumentException(sprintf(
      'Cannot create instances of the %s type from %s: %s',
      $this->getName(),
      typeof($value)->getName(),
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
    return null === $value ? null : $this->verified($value, function($m) use($value) { throw new ClassCastException(sprintf(
      'Cannot cast %s to the %s type: %s',
      typeof($value)->getName(),
      $this->getName(),
      $m
    )); });
  }

  /** Tests whether this type is assignable from another type */
  public function isAssignableFrom($type): bool {
    $t= $type instanceof Type ? $type : Type::forName($type);
    if ($t === Type::$CALLABLE) return true;
    if (!($t instanceof self) || !$this->returns->isAssignableFrom($t->returns)) return false;
    if (null === $this->signature) return true;
    if (sizeof($t->signature) !== sizeof($this->signature)) return false;
    foreach ($this->signature as $i => $type) {
      if (!$type->isAssignableFrom($t->signature[$i])) return false;
    }
    return true;
  }

  /**
   * Invokes a given function with the given arguments.
   *
   * @param   var $func
   * @param   var[] $args
   * @return  var
   * @throws  lang.IllegalArgumentException in case the passed function is not an instance of this type
   * @throws  lang.reflect.TargetInvocationException for any exception raised from the invoked function
   */
  public function invoke($func, $args= []) {
    $closure= $this->verified($func, function($m) use($func) { throw new IllegalArgumentException(sprintf(
      'Passed argument is not of a %s type (%s): %s',
      $this->getName(),
      typeof($func)->getName(),
      $m
    )); });
    try {
      return $closure(...$args);
    } catch (Throwable $e) {
      throw new \lang\reflect\TargetInvocationException($this->getName(), $e);
    }
  }
}
