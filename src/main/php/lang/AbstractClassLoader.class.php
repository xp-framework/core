<?php namespace lang;

/** 
 * Loads a class from the filesystem
 * 
 * @test  xp://net.xp_framework.unittest.reflection.ClassLoaderTest
 * @see   xp://lang.XPClass#forName
 */
abstract class AbstractClassLoader extends Object implements IClassLoader {
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
      throw new \ClassNotFoundException('URI:'.$uri);
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
    if (isset(\xp::$cl[$class])) return literal($class);

    // Load class
    $package= null;
    \xp::$cl[$class]= $this->getClassName().'://'.$this->path;
    \xp::$cll++;
    try {
      $r= include($this->classUri($class));
    } catch (ClassLoadingException $e) {
      unset(\xp::$cl[$class]);
      \xp::$cll--;

      $decl= null;
      if (null === $package) {
        $decl= substr($class, (false === ($p= strrpos($class, '.')) ? 0 : $p + 1));
      } else {
        $decl= strtr($class, '.', '·');
      }

      // If class was declared, but loading threw an exception it means
      // a "soft" dependency, one that is only required at runtime, was
      // not loaded, the class itself has been declared.
      if (class_exists($decl, false) || interface_exists($decl, false)) {
        raise('lang.ClassDependencyException', $class, [$this], $e);
      }

      // If otherwise, a "hard" dependency could not be loaded, eg. the
      // base class or a required interface and thus the class could not
      // be declared.
      raise('lang.ClassLinkageException', $class, [$this], $e);
    }
    \xp::$cll--;
    if (false === $r) {
      unset(\xp::$cl[$class]);
      $e= new ClassNotFoundException($class, [$this]);
      \xp::gc(__FILE__);
      throw $e;
    }
    
    // Register class name / literal mapping, which is one of the following:
    //
    // * No dot in the qualified class name -> ClassName
    // * Dotted version declares $package -> com·example·ClassName, alias as com\example\ClassName
    // * Dotted version resolves to a namespaced class -> com\example\ClassName
    // * Global namespace -> ClassName, alias as com\example\ClassName
    // 
    // Alias lang.** classes into global namespace
    if (false === ($p= strrpos($class, '.'))) {
      $name= $class;
      \xp::$sn[$class]= $name;
    } else if (null !== $package) {
      $name= strtr($class, '.', '·');
      class_alias($name, strtr($class, '.', '\\'));
      \xp::$sn[$class]= $name;
    } else if (($ns= strtr($class, '.', '\\')) && (class_exists($ns, false) || interface_exists($ns, false))) {
      $name= $ns;
    } else if (($cl= substr($class, $p+ 1)) && (class_exists($cl, false) || interface_exists($cl, false))) {
      $name= $cl;
      class_alias($name, strtr($class, '.', '\\'));
      \xp::$sn[$class]= $name;
    } else {
      unset(\xp::$cl[$class]);
      raise('lang.ClassFormatException', 'Class "'.$class.'" not declared in loaded file');
    }

    if (0 === strncmp($class, 'lang.', 5)) {
      class_alias($name, substr($class, $p + 1));
      \xp::$cn[substr($class, $p + 1)]= $class;
    }

    method_exists($name, '__static') && \xp::$cli[]= [$name, '__static'];
    if (0 === \xp::$cll) {
      $invocations= \xp::$cli;
      \xp::$cli= [];
      foreach ($invocations as $inv) call_user_func($inv, $name);
    }
    return $name;
  }

  /**
   * Checks whether two class loaders are equal
   *
   * @param   lang.Generic cmp
   * @return  bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $cmp->path === $this->path;
  }

  /**
   * Returns a hashcode for this class loader
   *
   * @return string
   */
  public function hashCode() {
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
   * Creates a string representation
   *
   * @return  string
   */
  public function toString() {
    $segments= explode(DIRECTORY_SEPARATOR, $this->path);
    if (sizeof($segments) > 6) {
      $path= '...'.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, array_slice($segments, -6));
    } else {
      $path= $this->path;
    }
    return str_replace('ClassLoader', 'CL', $this->getClass()->getSimpleName()).'<'.$path.'>';
  }
}
