<?php namespace lang;

/** 
 * Loads a class from the filesystem
 * 
 * @test  xp://net.xp_framework.unittest.reflection.ClassLoaderTest
 * @see   xp://lang.XPClass#forName
 */
abstract class AbstractClassLoader implements IClassLoader {
  public $path= '';
  
  /**
   * Load the class by the specified name
   *
   * @param   string class fully qualified class name io.File
   * @return  lang.XPClass
   * @throws  lang.ClassNotFoundException in case the class can not be found
   */
  public function loadClass($class) {
    return new XPClass($this->loadClass0($class));
  }
  
  /**
   * Returns URI suitable for include() given a class name
   *
   * @param   string class
   * @return  string
   */
  protected abstract function classUri($class);

  /**
   * Return a class at the given URI
   *
   * @param   string uri
   * @return  string fully qualified class name, or NULL
   */
  protected abstract function classAtUri($uri);

  /**
   * Return whether this class loader provides a given class via URI
   *
   * @param   string uri
   * @return  bool
   */
  public function providesUri($uri) {
    return null !== $this->classAtUri($uri);
  }

  /**
   * Find the class by a given URI
   *
   * @param   string uri
   * @return  lang.XPClass
   * @throws  lang.ClassNotFoundException in case the class can not be found
   */
  public function loadUri($uri) {
    if (null === ($class= $this->classAtUri($uri))) {
      throw new ClassNotFoundException('URI:'.$uri);
    }
    return $this->loadClass($class);
  }


  /**
   * Load the class by the specified name
   *
   * @param   string class fully qualified class name io.File
   * @return  string class name
   * @throws  lang.ClassNotFoundException in case the class can not be found
   * @throws  lang.ClassFormatException in case the class format is invalud
   */
  public function loadClass0($class) {
    $name= strtr($class, '.', '\\');
    if (isset(\xp::$cl[$class])) return $name;

    // Load class
    \xp::$cl[$class]= nameof($this).'://'.$this->path;
    \xp::$cll++;
    try {
      echo "L ", $this->classUri($class), "\n";
      $r= include($this->classUri($class));
    } catch (ClassLoadingException $e) {
      unset(\xp::$cl[$class]);
      \xp::$cll--;

      // If class was declared, but loading threw an exception it means
      // a "soft" dependency, one that is only required at runtime, was
      // not loaded, the class itself has been declared.
      if (class_exists($name, false) || interface_exists($name, false) || trait_exists($name, false)) {
        throw new ClassDependencyException($class, [$this], $e);
      }

      // If otherwise, a "hard" dependency could not be loaded, eg. the
      // base class or a required interface and thus the class could not
      // be declared.
      throw new ClassLinkageException($class, [$this], $e);
    }
    \xp::$cll--;

    if (false === $r) {
      unset(\xp::$cl[$class]);
      $e= new ClassNotFoundException($class, [$this]);
      \xp::gc(__FILE__);
      throw $e;
    } else if (!class_exists($name, false) && !interface_exists($name, false) && !trait_exists($name, false)) {
      $details= XPClass::detailsForClass($class);
      $uri= $this->classUri($class);
      unset(\xp::$cl[$class]);
      if (isset($details['class'])) {
        $reflect= new \ReflectionClass($details['class'][DETAIL_ARGUMENTS]);
        \xp::$errors[$uri][$reflect->getStartLine()]= ['' => 1];
        $e= new ClassFormatException('File does not declare type `'.$class.'`, but `'.strtr($reflect->getName(), '\\', '.').'`');
        \xp::gc($uri);
        throw $e;
      } else {
        throw new ClassFormatException('Loading `'.$class.'`: No types declared in '.$this->classUri($class));
      }
    }

    method_exists($name, '__static') && \xp::$cli[]= [$name, '__static'];
    if (0 === \xp::$cll) {
      $invocations= \xp::$cli;
      \xp::$cli= [];
      foreach ($invocations as $inv) $inv($name);
    }
    return $name;
  }

  /** Compares this class loader to another value */
  public function compareTo($value): int {
    return $value instanceof self ? $value->path <=> $this->path : 1;
  }

  /** Returns a hashcode for this class loader */
  public function hashCode(): string {
    return 'cl@'.$this->path;
  }

  /**
   * Returns a unique identifier for this class loader instance
   *
   * @return  string
   */
  public function instanceId() {
    return $this->path;
  }

  /**
   * Compacts paths by replacing base directories by placeholders
   *
   * @param  string $path
   * @param  [:string] $bases
   * @return string
   */
  private function compactPath($path, $bases) {
    foreach ($bases as $base => $replace) {
      $base= realpath($base);
      if (false !== $base && 0 === strpos($path, $base)) {
        $path= $replace.substr($path, strlen($base));
      }
    }
    return $path;
  }

  /**
   * Creates a string representation
   *
   * @return  string
   */
  public function toString() {
    if ($home= getenv('HOME')) {    // Un*x or Cygwin
      $separator= '/';
      $bases= [getcwd() => '.', $home => '~', getenv('APPDATA') => '$APPDATA'];
    } else if (0 === strncasecmp(PHP_OS, 'Win', 3)) {
      $separator= '\\';
      $bases= [getcwd() => '.', getenv('APPDATA') => '%APPDATA%'];
    } else {
      $separator= DIRECTORY_SEPARATOR;
      $bases= [getcwd() => '.'];
    }

    $path= $this->compactPath(rtrim($this->path, DIRECTORY_SEPARATOR), $bases);
    return
      str_replace('ClassLoader', 'CL', typeof($this)->getSimpleName()).
      '<'.strtr($path, DIRECTORY_SEPARATOR, $separator).'>'
    ;
  }
}