<?php

define('MODIFIER_STATIC',       1);
define('MODIFIER_ABSTRACT',     2);
define('MODIFIER_FINAL',        4);
define('MODIFIER_PUBLIC',     256);
define('MODIFIER_PROTECTED',  512);
define('MODIFIER_PRIVATE',   1024);

// {{{ trait xp
trait __xp {

  // {{{ static invocation handler
  public static function __callStatic($name, $args) {
    $self= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'];
    throw new Error('Call to undefined static method '.\xp::nameOf($self).'::'.$name.'()');
  }
  // }}}

  // {{{ field read handler
  public function __get($name) {
    return null;
  }
  // }}}

  // {{{ field write handler
  public function __set($name, $value) {
    $this->{$name}= $value;
  }
  // }}}

  // {{{ invocation handler
  public function __call($name, $args) {
    $t= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
    $self= $t[1]['class'];

    // Check scope for extension methods
    $scope= isset($t[2]['class']) ? $t[2]['class'] : $t[3]['class'];
    if (null != $scope && isset(\xp::$ext[$scope])) {
      foreach (\xp::$ext[$scope] as $type => $class) {
        if (!$this instanceof $type || !method_exists($class, $name)) continue;
        array_unshift($args, $this);
        return call_user_func_array([$class, $name], $args);
      }
    }

    throw new Error('Call to undefined method '.\xp::nameOf($self).'::'.$name.'() from scope '.\xp::nameOf($scope));
  }
  // }}}

}
// }}}

// {{{ final class xp
final class xp {
  const CLASS_FILE_EXT= '.class.php';
  const ENCODING= 'utf-8';

  public static $ext= [];
  public static $cli= [];
  public static $cll= 0;
  public static $cl= [];
  public static $cn= [
    'xp'    => '<xp>',
    'null'  => '<null>'
  ];
  public static $null= null;
  public static $loader= null;
  public static $classpath= null;
  public static $errors= [];
  public static $meta= [];
  public static $registry= [];
  
  function findClass($class) {
    return $this;
  }

  // {{{ proto string loadClass0(string name)
  //     Loads a class by its fully qualified name
  function loadClass0($class) {
    if (isset(xp::$cl[$class])) return array_search($class, xp::$cn, true);
    foreach (xp::$classpath as $path) {

      // We rely on paths having been expanded including a trailing directory separator
      // character inside bootstrap(). This way, we can save testing for whether the path
      // entry is a directory with file system stat() calls.
      if (DIRECTORY_SEPARATOR === $path{strlen($path) - 1}) {
        $f= $path.strtr($class, '.', DIRECTORY_SEPARATOR).xp::CLASS_FILE_EXT;
        $cl= 'lang.FileSystemClassLoader';
      } else {
        $f= 'xar://'.$path.'?'.strtr($class, '.', '/').xp::CLASS_FILE_EXT;
        $cl= 'lang.archive.ArchiveClassLoader';
      }
      if (!file_exists($f)) continue;

      // Load class
      $package= null;
      xp::$cl[$class]= $cl.'://'.$path;
      xp::$cll++;
      $r= include($f);
      xp::$cll--;
      if (false === $r) {
        unset(xp::$cl[$class]);
        continue;
      }

      // Register class name and call static initializer if available
      if (false === ($p= strrpos($class, '.'))) {
        $name= $class;
      } else if (null !== $package) {
        $name= strtr($class, '.', '·');
        class_alias($name, strtr($class, '.', '\\'));
      } else {
        $name= strtr($class, '.', '\\');
      }

      xp::$cn[$name]= $class;

      if (0 === strncmp($class, 'lang.', 5)) {
        $short= substr($class, $p + 1);
        class_alias($name, $short);
        xp::$cn[$short]= $class;
      }

      method_exists($name, '__static') && xp::$cli[]= [$name, '__static'];
      if (0 === xp::$cll) {
        $invocations= xp::$cli;
        xp::$cli= [];
        foreach ($invocations as $inv) call_user_func($inv, $name);
      }

      return $name;
    }
    
    xp::error('Cannot bootstrap class '.$class.' (include_path= '.get_include_path().')');
  }
  // }}}

  // {{{ proto string nameOf(string name)
  //     Returns the fully qualified name
  static function nameOf($name) {
    return isset(xp::$cn[$name]) ? xp::$cn[$name] : 'php.'.$name;
  }
  // }}}

  // {{{ proto string typeOf(var arg)
  //     Returns the fully qualified type name
  static function typeOf($arg) {
    return is_object($arg) ? xp::nameOf(get_class($arg)) : gettype($arg);
  }
  // }}}

  // {{{ proto string stringOf(var arg [, string indent default ''])
  //     Returns a string representation of the given argument
  static function stringOf($arg, $indent= '') {
    static $protect= [];
    
    if (is_string($arg)) {
      return '"'.$arg.'"';
    } else if (is_bool($arg)) {
      return $arg ? 'true' : 'false';
    } else if (is_null($arg)) {
      return 'null';
    } else if ($arg instanceof null) {
      return '<null>';
    } else if (is_int($arg) || is_float($arg)) {
      return (string)$arg;
    } else if ($arg instanceof \lang\Generic && !isset($protect[(string)$arg->hashCode()])) {
      $protect[(string)$arg->hashCode()]= true;
      $s= $arg->toString();
      unset($protect[(string)$arg->hashCode()]);
      return $indent ? str_replace("\n", "\n".$indent, $s) : $s;
    } else if (is_array($arg)) {
      $ser= print_r($arg, true);
      if (isset($protect[$ser])) return '->{:recursion:}';
      $protect[$ser]= true;
      if (0 === key($arg)) {
        $r= '';
        foreach ($arg as $val) {
          $r.= ', '.xp::stringOf($val);
        }
        unset($protect[$ser]);
        return '['.substr($r, 2).']';
      } else {
        $r= "[\n";
        foreach (array_keys($arg) as $key) {
          $r.= $indent.'  '.$key.' => '.xp::stringOf($arg[$key], $indent.'  ')."\n";
        }
        unset($protect[$ser]);
        return $r.$indent.']';
      }
    } else if ($arg instanceof \Closure) {
      $sig= '';
      $f= new \ReflectionFunction($arg);
      foreach ($f->getParameters() as $p) {
        $sig.= ', $'.$p->name;
      }
      return '<function('.substr($sig, 2).')>';
    } else if (is_object($arg)) {
      $ser= spl_object_hash($arg);
      if (isset($protect[$ser])) return '->{:recursion:}';
      $protect[$ser]= true;
      $r= xp::nameOf(get_class($arg))." {\n";
      $vars= (array)$arg;
      foreach (array_keys($vars) as $key) {
        $r.= $indent.'  '.$key.' => '.xp::stringOf($vars[$key], $indent.'  ')."\n";
      }
      unset($protect[$ser]);
      return $r.$indent.'}';
    } else if (is_resource($arg)) {
      return 'resource(type= '.get_resource_type($arg).', id= '.(int)$arg.')';
    }
  }
  // }}}

  // {{{ proto void extensions(string class, string scope)
  //     Registers extension methods for a certain scope
  static function extensions($class, $scope) {
    foreach ((new \lang\XPClass($class))->getMethods() as $method) {
      if (MODIFIER_STATIC & $method->getModifiers() && $method->numParameters() > 0) {
        $param= $method->getParameter(0);
        if ('self' === $param->getName()) {
          xp::$ext[$scope][$param->getType()->literal()]= $class;
        }
      }
    }
  }
  // }}}

  // {{{ proto void gc([string file default null])
  //     Runs the garbage collector
  static function gc($file= null) {
    if ($file) {
      unset(xp::$errors[$file]);
    } else {
      xp::$errors= [];
    }
  }
  // }}}

  // {{{ proto <null> null()
  //     Runs a fatal-error safe version of null
  static function null() {
    return xp::$null;
  }
  // }}}

  // {{{ proto var errorAt(string file [, int line])
  //     Returns errors that occured at the specified position or null
  static function errorAt($file, $line= -1) {
    if ($line < 0) {    // If no line is given, check for an error in the file
      return isset(xp::$errors[$file]) ? xp::$errors[$file] : null;
    } else {            // Otherwise, check for an error in the file on a certain line
      return isset(xp::$errors[$file][$line]) ? xp::$errors[$file][$line] : null;
    }
  }
  // }}}
  
  // {{{ proto string reflect(string type)
  //     Retrieve type literal for a given type name
  static function reflect($type) {
    if ('string' === $type || 'int' === $type || 'double' === $type || 'bool' == $type) {
      return 'þ'.$type;
    } else if ('var' === $type) {
      return $type;
    } else if ('[]' === substr($type, -2)) {
      return '¦'.xp::reflect(substr($type, 0, -2));
    } else if ('[:' === substr($type, 0, 2)) {
      return '»'.xp::reflect(substr($type, 2, -1));
    } else if (false !== ($p= strpos($type, '<'))) {
      $l= xp::reflect(substr($type, 0, $p)).'··';
      for ($args= substr($type, $p+ 1, -1).',', $o= 0, $brackets= 0, $i= 0, $s= strlen($args); $i < $s; $i++) {
        if (',' === $args{$i} && 0 === $brackets) {
          $l.= strtr(xp::reflect(ltrim(substr($args, $o, $i- $o))).'¸', '\\', '¦');
          $o= $i+ 1;
        } else if ('<' === $args{$i}) {
          $brackets++;
        } else if ('>' === $args{$i}) {
          $brackets--;
        }
      }
      return substr($l, 0, -1);
    } else {
      $l= array_search($type, xp::$cn, true);
      return $l ?: substr($type, (false === $p= strrpos($type, '.')) ? 0 : $p+ 1);
    }
  }
  // }}}

  // {{{ proto void error(string message)
  //     Throws a fatal error and exits with exitcode 61
  static function error($message) {
    restore_error_handler();
    trigger_error($message, E_USER_ERROR);
    exit(0x3d);
  }
  // }}}

  // {{{ proto string version()
  //     Retrieves current XP version
  static function version() {
    static $version= null;

    if (null === $version) {
      $version= trim(\lang\ClassLoader::getDefault()->getResource('VERSION'));
    }
    return $version;
  }
  // }}}
}
// }}}

// {{{ final class null
final class null {

  // {{{ proto __construct(void)
  //     Constructor to avoid magic __call invokation
  public function __construct() {
    if (isset(xp::$null)) {
      throw new \lang\IllegalAccessException('Cannot create new instances of xp::null()');
    }
  }
  
  // {{{ proto void __clone(void)
  //     Clone interceptor
  public function __clone() {
    throw new \lang\NullPointerException('Object cloning intercepted.');
  }
  // }}}
  
  // {{{ proto var __call(string name, var[] args)
  //     Call proxy
  function __call($name, $args) {
    throw new \lang\NullPointerException('Method.invokation('.$name.')');
  }
  // }}}

  // {{{ proto void __set(string name, var value)
  //     Set proxy
  function __set($name, $value) {
    throw new \lang\NullPointerException('Property.write('.$name.')');
  }
  // }}}

  // {{{ proto var __get(string name)
  //     Set proxy
  function __get($name) {
    throw new \lang\NullPointerException('Property.read('.$name.')');
  }
  // }}}
}
// }}}

// {{{ proto void __error(int code, string msg, string file, int line)
//     Error callback
function __error($code, $msg, $file, $line) {
  if (0 === error_reporting() || is_null($file)) return;

  if (E_RECOVERABLE_ERROR === $code) {
    throw new \lang\IllegalArgumentException($msg.' @ '.$file.':'.$line);
  } else {
    $bt= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $class= (isset($bt[1]['class']) ? $bt[1]['class'] : null);
    $method= (isset($bt[1]['function']) ? $bt[1]['function'] : null);
    
    if (!isset(xp::$errors[$file][$line][$msg])) {
      xp::$errors[$file][$line][$msg]= [
        'class'   => $class,
        'method'  => $method,
        'cnt'     => 1
      ];
    } else {
      xp::$errors[$file][$line][$msg]['cnt']++;
    }
  }
}
// }}}

// {{{ proto void uses (string* args)
//     Uses one or more classes
function uses() {
  $scope= null;
  foreach (func_get_args() as $str) {
    $class= xp::$loader->loadClass0($str);

    // Tricky: We can arrive at this point without the class actually existing:
    // A : uses("B")
    // `-- B : uses("A")
    //     `--> A : We are here, class A not complete!
    // "Wait" until we unwind the stack until the first position so A is
    // "complete" before calling __import.
    // Check with class_exists(), because method_exists() triggers autoloading.
    if (class_exists($class, false) && method_exists($class, '__import')) {
      if (null === $scope) {
        $trace= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $scope= xp::reflect($trace[2]['args'][0]);
      }
      call_user_func([$class, '__import'], $scope);
    }
  }
}
// }}}

// {{{ proto void raise (string classname, var* args)
//     throws an exception by a given class name
function raise($classname) {
  try {
    $class= \lang\XPClass::forName($classname);
  } catch (ClassNotFoundException $e) {
    xp::error($e->getMessage());
  }
  
  $a= func_get_args();
  throw call_user_func_array([$class, 'newInstance'], array_slice($a, 1));
}
// }}}

// {{{ proto void ensure ($t)
//     Replacement for finally() which clashes with PHP 5.5.0's finally
function ensure(&$t) {
  if (!isset($t)) $t= null;
}
// }}}

// {{{ proto Generic cast (Generic expression, string type)
//     Casts an expression.
function cast(Generic $expression= null, $type) {
  if (null === $expression) {
    return xp::null();
  } else if (\lang\XPClass::forName($type)->isInstance($expression)) {
    return $expression;
  }

  raise('lang.ClassCastException', 'Cannot cast '.xp::typeOf($expression).' to '.$type);
}

// {{{ proto bool is(string type, var object)
//     Checks whether a given object is an instance of the type given
function is($type, $object) {
  if (null === $type) {
    return $object instanceof null;
  } else if ('int' === $type) {
    return is_int($object);
  } else if ('double' === $type) {
    return is_double($object);
  } else if ('string' === $type) {
    return is_string($object);
  } else if ('bool' === $type) {
    return is_bool($object);
  } else if ('var' === $type) {
    return true;
  } else if (0 === substr_compare($type, '[]', -2)) {
    return (new \lang\ArrayType(substr($type, 0, -2)))->isInstance($object);
  } else if (0 === substr_compare($type, '[:', 0, 2)) {
    return (new \lang\MapType(substr($type, 2, -1)))->isInstance($object);
  } else if (strstr($type, '?')) {
    return \lang\WildcardType::forName($type)->isInstance($object);
  } else {
    $type= xp::reflect($type);
    return $object instanceof $type;
  }
}
// }}}

// {{{ proto void delete(&var object)
//     Destroys an object
function delete(&$object) {
  $object= null;
}
// }}}

// {{{ proto void with(arg..., Closure)
//     Syntactic sugar. Intentionally empty
function with() {
  $args= func_get_args();
  if (($block= array_pop($args)) instanceof \Closure)  {
    try {
      call_user_func_array($block, $args);
    } catch (\lang\Throwable $e) {
      // Fall through
    } ensure($e); {
      foreach ($args as $arg) {
        if (!($arg instanceof \lang\Closeable)) continue;
        try {
          $arg->close();
        } catch (\lang\Throwable $ignored) {
          // 
        }
      }
      if ($e) throw($e);
    }
  }
}
// }}}

// {{{ proto var this(var expr, var offset)
//     Indexer access for a given expression
function this($expr, $offset) {
  return $expr[$offset];
}
// }}}

// {{{ proto lang.Object newinstance(string spec, var[] args, var def)
//     Anonymous instance creation
function newinstance($spec, $args, $def= null) {
  static $u= 0;

  if ('#' === $spec{0}) {
    $p= strrpos($spec, ' ');
    $annotations= substr($spec, 0, $p).' ';
    $spec= substr($spec, $p+ 1);
  } else {
    $annotations= '';
  }

  // Check for an anonymous generic 
  if (strstr($spec, '<')) {
    $class= Type::forName($spec);
    $type= $class->literal();
    $p= strrpos(substr($type, 0, strpos($type, '··')), '·');
    $generic= xp::$meta[$class->getName()]['class'][DETAIL_GENERIC];
  } else {
    false === strrpos($spec, '.') && $spec= xp::nameOf($spec);
    try {
      $type= 0 === strncmp($spec, 'php.', 4) ? substr($spec, 4) : xp::$loader->loadClass0($spec);
    } catch (ClassLoadingException $e) {
      xp::error($e->getMessage());
    }
    $p= strrpos($type, '·');
    $generic= null;
  }

  // Create unique name
  $n= '·'.(++$u);
  if (false !== $p) {
    $spec= strtr(substr($type, 0, $p), '·', '.').'.'.substr($type, $p+ 1).$n;
  } else {
    $spec= strtr($type, '\\', '.').$n;
  }

  if (interface_exists($type)) {
    $decl= 'class %s extends \\lang\\Object implements \\'.$type;
  } else {
    $decl= 'class %s extends \\'.$type;
  }
  $type= \lang\ClassLoader::defineType($annotations.$spec, $decl, $def);

  if ($generic) {
    \lang\XPClass::detailsForClass($spec);
    xp::$meta[$spec]['class'][DETAIL_GENERIC]= $generic;
  }

  if ($type->hasConstructor()) {
    return $type->getConstructor()->newInstance($args);
  } else {
    return $type->newInstance();
  }
}
// }}}

// {{{ proto lang.Generic create(var spec)
//     Creates a generic object
function create($spec) {
  if ($spec instanceof \lang\Generic) return $spec;

  // Parse type specification: "new " TYPE "()"?
  // TYPE:= B "<" ARGS ">"
  // ARGS:= TYPE [ "," TYPE [ "," ... ]]
  $b= strpos($spec, '<');
  $base= substr($spec, 4, $b- 4);
  $typeargs= \lang\Type::forNames(substr($spec, $b+ 1, strrpos($spec, '>')- $b- 1));
  
  // Instantiate, passing the rest of any arguments passed to create()
  // BC: Wrap IllegalStateExceptions into IllegalArgumentExceptions
  $class= \lang\XPClass::forName(strstr($base, '.') ? $base : xp::nameOf($base));
  try {
    $reflect= $class->newGenericType($typeargs)->_reflect;
    if ($reflect->hasMethod('__construct')) {
      $a= func_get_args();
      return $reflect->newInstanceArgs(array_slice($a, 1));
    } else {
      return $reflect->newInstance();
    }
  } catch (\lang\IllegalStateException $e) {
    throw new \lang\IllegalArgumentException($e->getMessage());
  } catch (ReflectionException $e) {
    throw new \lang\IllegalAccessException($e->getMessage());
  }
}
// }}}

// {{{ proto lang.Type typeof(mixed arg)
//     Returns type
function typeof($arg) {
  if ($arg instanceof \lang\Generic) {
    return $arg->getClass();
  } else if (null === $arg) {
    return \lang\Type::$VOID;
  } else if ($arg instanceof \Closure) {
    $r= new \ReflectionFunction($arg);
    $signature= [];
    foreach ($r->getParameters() as $param) {
      if ($param->isArray()) {
        $signature[]= \lang\Primitive::$ARRAY;
      } else if ($param->isCallable()) {
        $signature[]= new \lang\FunctionType(null, \lang\Type::$VAR); 
      } else if (null === ($class= $param->getClass())) {
        $signature[]= \lang\Type::$VAR;
      } else {
        $signature[]= new \lang\XPClass($class);
      }
    }
    return new \lang\FunctionType($signature, \lang\Type::$VAR);
  } else if (is_array($arg)) {
    return 0 === key($arg) ? \lang\ArrayType::forName('var[]') : \lang\MapType::forName('[:var]');
  } else {
    return \lang\Type::forName(gettype($arg));
  }
}
// }}}

// {{{ proto bool __load(string class)
//     SPL Autoload callback
function __load($class) {
  $name= strtr($class, '\\', '.');
  $cl= xp::$loader->findClass($name);
  if ($cl instanceof null) return false;
  $cl->loadClass0($name);
  return true;
}
// }}}

// {{{ class import
class import {
  function __construct($str) {
    $class= xp::$loader->loadClass0($str);
    xp::$cli[]= function($scope) use ($class) {
      $class::__import($scope);
    };
  }
}
// }}}

// {{{ proto bool __import(string class)
//     SPL Autoload callback
function __import($class) {
  if (false === strrpos($class, '\\import')) {
    return false;
  } else {
    class_alias('import', $class);
    return true;
  }
}
// }}}

// {{{ initialization
error_reporting(E_ALL);

// Constants
define('LONG_MAX', PHP_INT_MAX);
define('LONG_MIN', -PHP_INT_MAX - 1);

// Hooks
spl_autoload_register('__load');
spl_autoload_register('__import');
set_error_handler('__error');

// Verify timezone
date_default_timezone_set(ini_get('date.timezone')) || xp::error('[xp::core] date.timezone not configured properly.');

// Registry initialization
ini_set('display_errors', 'false');
xp::$null= new null();
xp::$loader= new xp();
xp::$classpath= $paths;
set_include_path(rtrim(implode(PATH_SEPARATOR, $paths), PATH_SEPARATOR));
// }}}
