<?php namespace lang;
 
/**
 * Represents classes. Every instance of an XP class has a method
 * called getClass() which returns an instance of this class.
 *
 * Examples
 * ========
 * To retrieve the fully qualified name of a class, use this:
 * ```php
 * $file= new File('...');
 * echo 'The class name for $file is '.typeof($file)->getName();
 * ```
 *
 * Create an instance of a class:
 * ```php
 * $instance= XPClass::forName('util.Binford')->newInstance();
 * ```
 *
 * @see   lang.XPClass#forName
 * @test  lang.unittest.XPClassTest
 * @test  lang.unittest.IsInstanceTest
 * @test  lang.unittest.ClassCastingTest
 */
class XPClass extends Type {
  private $_class;
  private $_reflect= null;

  static function __static() {

    // Workaround for missing detail information about return types in
    // builtin classes.
    \xp::$meta['php.Exception']= [
      'class' => [4 => null, []],
      0 => [],
      1 => [
        'getMessage'       => [1 => [], 'string', [], null, []],
        'getCode'          => [1 => [], 'int', [], null, []],
        'getFile'          => [1 => [], 'string', [], null, []],
        'getLine'          => [1 => [], 'int', [], null, []],
        'getTrace'         => [1 => [], 'var[]', [], null, []],
        'getPrevious'      => [1 => [], 'lang.Throwable', [], null, []],
        'getTraceAsString' => [1 => [], 'string', [], null, []]
      ]
    ];
  }

  /**
   * Constructor
   *
   * @param  string|ReflectionClass|object $ref
   */
  public function __construct($ref) {
    if ($ref instanceof \ReflectionClass) {
      $this->_class= $ref->getName();
      $this->_reflect= $ref;
    } else if ($ref instanceof \__PHP_Incomplete_Class) {
      throw new ClassCastException('Cannot use incomplete classes in reflection');
    } else if (is_object($ref)) {
      $this->_class= get_class($ref);
    } else {
      $this->_class= (string)$ref;
    }
    parent::__construct(self::nameOf($this->_class), null);
  }

  /**
   * Returns the reflection object lazily initialized
   *
   * @return php.ReflectionClass
   * @throws  lang.IllegalStateException
   */
  public function reflect() {
    if (null === $this->_reflect) {
      try {
        $this->_reflect= new \ReflectionClass($this->_class);
      } catch (\ReflectionException $e) {
        throw new IllegalStateException($e->getMessage());
      }
    }
    return $this->_reflect;
  }

  /**
   * Returns XP name of a given PHP name.
   *
   * @param  string $class
   * @return string
   */
  public static function nameOf($class) {
    if (isset(\xp::$cn[$class])) {
      return \xp::$cn[$class];
    } else if (strstr($class, '\\')) {
      return strtr($class, '\\', '.');
    } else {
      $name= array_search($class, \xp::$sn, true);
      return false === $name ? $class : $name;
    }
  }

  /** Returns type literal */
  public function literal(): string {
    return literal($this->name);
  }

  /** Returns declared name */
  public function declaredName(): string {
    return false === ($p= strrpos(substr($this->name, 0, strcspn($this->name, '<')), '.')) 
      ? $this->name
      : substr($this->name, $p + 1)
    ;
  }

  /** Returns package name (or NULL for the global package) */
  public function packageName(): ?string {
    return false === ($p= strrpos(substr($this->name, 0, strcspn($this->name, '<')), '.'))
      ? null
      : substr($this->name, 0, $p)
    ;
  }

  /**
   * Creates a new instance of the class represented by this Class object.
   * The class is instantiated as if by a new expression with an empty argument list.
   *
   * Example
   * =======
   * <code>
   *   try {
   *     $o= XPClass::forName($name)->newInstance();
   *   } catch (ClassNotFoundException $e) {
   *     // handle it!
   *   }
   * </code>
   *
   * Example (passing arguments)
   * ===========================
   * <code>
   *   try {
   *     $o= XPClass::forName('peer.Socket')->newInstance('localhost', 6100);
   *   } catch (ClassNotFoundException $e) {
   *     // handle it!
   *   }
   * </code>
   *
   * @param   var... $args
   * @return  object
   * @throws  lang.IllegalAccessException in case this class cannot be instantiated
   */
  public function newInstance(... $args) {
    $reflect= $this->reflect();
    if ($reflect->isInterface()) {
      throw new IllegalAccessException('Cannot instantiate interfaces ('.$this->name.')');
    } else if ($reflect->isTrait()) {
      throw new IllegalAccessException('Cannot instantiate traits ('.$this->name.')');
    } else if ($reflect->isAbstract()) {
      throw new IllegalAccessException('Cannot instantiate abstract classes ('.$this->name.')');
    }

    try {
      return $reflect->newInstance(...$args);
    } catch (\ReflectionException $e) {
      throw new IllegalAccessException($e->getMessage(), $e);
    }
  }

  /**
   * Cast a given object to the class represented by this object
   *
   * @param   var value
   * @return  var the given expression
   * @throws  lang.ClassCastException
   */
  public function cast($value) {
    if (null === $value) return null;

    $literal= literal($this->name);
    if ($value instanceof $literal) {
      return $value;
    } else {
      throw new ClassCastException('Cannot cast '.typeof($value)->getName().' to '.$this->name);
    }
  }

  /**
   * Tests whether this class is assignable from a given type
   *
   * ```php
   * // util.Date "instanceof" lang.Value
   * XPClass::forName('lang.Value')->isAssignableFrom('util.Date');   // TRUE
   * ```
   *
   * @param   string|lang.Type $type
   * @return  bool
   */
  public function isAssignableFrom($type): bool {
    $t= $type instanceof Type ? $type : Type::forName($type);
    return $t instanceof self
      ? $t->name === $this->name || $t->reflect()->isSubclassOf($this->reflect())
      : false
    ;
  }

  /**
   * Determines whether the specified object is an instance of this
   * class. This is the equivalent of the `instance()` core functionality.
   *
   * ```php
   * $class= XPClass::forName('io.File');
   * 
   * var_dump($class->isInstance(new TempFile()));  // TRUE
   * var_dump($class->isInstance(new File()));      // TRUE
   * var_dump($class->isInstance(new Date()));      // FALSE
   * ```
   *
   * @param   var obj
   * @return  bool
   */
  public function isInstance($obj): bool {
    return instance($this->name, $obj);
  }

  /** Retrieve the class loader a class was loaded with */
  public function getClassLoader(): ?IClassLoader {
    if (isset(\xp::$cl[$this->name])) {
      sscanf(\xp::$cl[$this->name], '%[^:]://%[^$]', $cl, $argument);
      $instanceFor= [literal($cl), 'instanceFor'];
      return $instanceFor($argument);
    }
    return null;    // Internal class, e.g.
  }

  /**
   * Returns `xp::$meta` for this class, extracting it if necessary
   *
   * @return [:var]
   */
  public function meta() {
    static $meta;
    return \xp::$meta[$this->name]??= ($meta??= new ClassMeta())->meta($this->_class);
  }

  /**
   * Reflectively creates a new type
   *
   * @param   lang.Type[] arguments
   * @return  lang.XPClass
   * @throws  lang.IllegalStateException if this class is not a generic definition
   * @throws  lang.IllegalArgumentException if number of arguments does not match components
   */
  public function newGenericType($arguments) {
    static $creator= null;

    $creator??= new GenericTypes();
    return $creator->newType($this, $arguments);
  }

  /**
   * Returns generic type components
   *
   * @return  string[]
   * @throws  lang.IllegalStateException if this class is not a generic definition
   */
  public function genericComponents() {
    if ($meta= \xp::$meta[$this->name] ?? null) {
      $arguments= $meta['class'][DETAIL_ANNOTATIONS]['generic'] ?? [];
    } else {
      $arguments= [];
      foreach ($this->reflect()->getAttributes(Generic::class) as $attribute) {
        $arguments+= $attribute->getArguments();
      }
    }

    if (!isset($arguments['self'])) {
      throw new IllegalStateException('Class '.$this->name.' is not a generic definition');
    }

    $components= [];
    foreach (explode(',', $arguments['self']) as $name) {
      $components[]= ltrim($name);
    }
    return $components;
  }

  /**
   * Returns whether this class is a generic definition
   *
   * @return  bool
   */
  public function isGenericDefinition(): bool {
    if ($meta= \xp::$meta[$this->name] ?? null) {
      return isset($meta['class'][DETAIL_ANNOTATIONS]['generic']['self']);
    } else {
      foreach ($this->reflect()->getAttributes(Generic::class) as $attribute) {
        if (isset($attribute->getArguments()['self'])) return true;
      }
      return false;
    }
  }

  /**
   * Returns generic type definition
   *
   * @return  lang.XPClass
   * @throws  lang.IllegalStateException if this class is not a generic
   */
  public function genericDefinition() {
    if ($generic= \xp::$meta[$this->name]['class'][DETAIL_GENERIC] ?? null) {
      return XPClass::forName($generic[0]);
    }

    throw new IllegalStateException('Class '.$this->name.' is not generic');
  }

  /**
   * Returns generic type arguments
   *
   * @return  lang.Type[]
   * @throws  lang.IllegalStateException if this class is not a generic
   */
  public function genericArguments() {
    if ($generic= \xp::$meta[$this->name]['class'][DETAIL_GENERIC] ?? null) {
      return $generic[1] ?? array_map([Type::class, 'forName'], $generic[2]);
    }

    throw new IllegalStateException('Class '.$this->name.' is not generic');
  }
      
  /** Returns whether this class is generic */
  public function isGeneric(): bool {
    return isset(\xp::$meta[$this->name]['class'][DETAIL_GENERIC]);
  }
  
  /**
   * Returns the XPClass object associated with the class with the given 
   * string name. Uses the default classloader if none is specified.
   *
   * @param   string name - e.g. "Exception", "io.File" or "lang.XPClass"
   * @param   ?lang.IClassLoader classloader default NULL
   * @return  lang.XPClass class object
   * @throws  lang.ClassNotFoundException when there is no such class
   */
  public static function forName($name, ?IClassLoader $classloader= null): self {
    $p= strpos($name, '\\');
    if (false === $p) {     // No backslashes, using dotted form
      $resolved= strtr($name, '.', '\\');
    } else {                // Name literal
      $resolved= 0 === $p ? substr($name, 1) : $name;
      $name= strtr($resolved, '\\', '.');
    }

    if (class_exists($resolved, false) || interface_exists($resolved, false) || trait_exists($resolved, false) || enum_exists($resolved, false)) {
      return new self($resolved);
    } else {
      return ($classloader ?? ClassLoader::getDefault())->loadClass($name);
    }
  }
  
  /** Returns all loaded classes */
  public static function getClasses(): \Traversable {
    foreach (\xp::$cl as $class => $loader) {
      yield new self(literal($class));
    }
  }
}
