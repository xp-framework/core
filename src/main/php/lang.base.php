<?php

// {{{ trait xp
trait __xp {

  // {{{ static invocation handler
  public static function __callStatic($name, $args) {
    $c= defined('HHVM_VERSION');
    $self= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1 - $c]['class'];
    throw new \lang\Error('Call to undefined static method '.\xp::nameOf($self).'::'.$name.'()');
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
    $c= defined('HHVM_VERSION');
    $self= $t[1 - $c]['class'];
    $scope= isset($t[2 - $c]['class']) ? $t[2 - $c]['class'] : $t[3 - $c]['class'];

    // Check scope for extension methods
    if (null != $scope && isset(\xp::$ext[$scope])) {
      foreach (\xp::$ext[$scope] as $type => $class) {
        if (!$this instanceof $type || !method_exists($class, $name)) continue;
        array_unshift($args, $this);
        return call_user_func_array([$class, $name], $args);
      }
    }

    throw new \lang\Error('Call to undefined method '.\xp::nameOf($self).'::'.$name.'() from scope '.\xp::nameOf($scope));
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
  public static $sn= [
    'xp'     => 'xp',
    'null'   => 'null',
    'string' => "\xfestring",
    'int'    => "\xfeint",
    'double' => "\xfedouble",
    'bool'   => "\xfebool",
    'var'    => "var",
  ];
  public static $null= null;
  public static $loader= null;
  public static $classpath= null;
  public static $errors= [];
  public static $meta= [];
  public static $registry= [];
  
  // {{{ proto lang.IClassLoader findClass(string class)
  //     Finds the class loader for a class by its fully qualified name
  function findClass($class) {
    return $this;
  }
  // }}}

  // {{{ proto string loadClass0(string class)
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
      xp::$cl[$class]= $cl.'://'.$path;
      xp::$cll++;
      $r= include($f);
      xp::$cll--;
      if (false === $r) {
        unset(xp::$cl[$class]);
        continue;
      }

      // Register class name and call static initializer if available
      $p= strrpos($class, '.');
      $name= strtr($class, '.', '\\');
      method_exists($name, '__static') && xp::$cli[]= [$name, '__static'];
      if (0 === xp::$cll) {
        $invocations= xp::$cli;
        xp::$cli= [];
        foreach ($invocations as $inv) $inv($name);
      }

      return $name;
    }
    
    xp::error('Cannot bootstrap class '.$class.' (include_path= '.get_include_path().')');
  }
  // }}}

  // {{{ proto string nameOf(string name)
  //     Returns the fully qualified name
  static function nameOf($name) {
    if (isset(xp::$cn[$name])) {
      return xp::$cn[$name];
    } else if (strstr($name, '\\')) {
      return strtr($name, '\\', '.');
    } else if ($result= array_search($name, \xp::$sn, true)) {
      return $result;
    } else {
      return 'php.'.$name;
    }
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
          $r.= ', '.xp::stringOf($val, $indent);
        }
        unset($protect[$ser]);
        return '['.substr($r, 2).']';
      } else {
        $r= "[\n";
        foreach ($arg as $key => $val) {
          $r.= $indent.'  '.$key.' => '.xp::stringOf($val, $indent.'  ')."\n";
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
      foreach ((array)$arg as $key => $val) {
        $r.= $indent.'  '.$key.' => '.xp::stringOf($val, $indent.'  ')."\n";
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

  // {{{ proto deprecated <null> null()
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
  
  // {{{ proto deprecated string reflect(string type)
  //     Retrieve type literal for a given type name
  static function reflect($type) {
    return literal($type);
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

// {{{ final deprecated class null
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
  if (0 === error_reporting() || null === $file) return;

  if (E_RECOVERABLE_ERROR === $code) {
    if (0 === strncmp($msg, 'Call', 4)) {
      throw new \lang\NullPointerException($msg);
    } else if (0 === strncmp($msg, 'Argument', 8)) {
      throw new \lang\IllegalArgumentException($msg);
    } else if (0 === strncmp($msg, 'Object', 6)) {
      throw new \lang\ClassCastException($msg);
    } else {
      throw new \lang\Error($msg);
    }
  } else if (0 === strncmp($msg, 'Undefined variable', 18)) {
    throw new \lang\NullPointerException($msg);
  } else if (0 === strncmp($msg, 'Missing argument', 16)) {
    throw new \lang\IllegalArgumentException($msg);
  } else if ((
    0 === strncmp($msg, 'Undefined offset', 16) ||
    0 === strncmp($msg, 'Undefined index', 15) ||
    0 === strncmp($msg, 'Uninitialized string', 20)
  )) {
    throw new \lang\IndexOutOfBoundsException($msg);
  } else if ('string conversion' === substr($msg, -17)) {
    throw new \lang\ClassCastException($msg);
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

// {{{ proto deprecated void uses (string* args)
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
        $scope= literal($trace[2]['args'][0]);
      }
      $class::__import($scope);
    }

    $short= substr($str, strrpos($str, '.') + 1);
    if (!class_exists($short, false) && !interface_exists($short, false)) {
      \xp::$cn[$short]= $str;
      class_alias($class, $short);
    }
  }
}
// }}}

// {{{ proto deprecated void raise (string classname, var* args)
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

// {{{ proto Generic cast (var arg, var type[, bool nullsafe= true])
//     Casts an arg NULL-safe
function cast($arg, $type, $nullsafe= true) {
  if (null === $arg && $nullsafe) {
    raise('lang.ClassCastException', 'Cannot cast NULL to '.$type);
  } else if ($type instanceof \lang\Type) {
    return $type->cast($arg);
  } else {
    return Type::forName($type)->cast($arg);
  }
}
// }}}

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
  } else if (0 === strncmp($type, 'function(', 9)) {
    return \lang\FunctionType::forName($type)->isInstance($object);
  } else if (strstr($type, '?')) {
    return \lang\WildcardType::forName($type)->isInstance($object);
  } else {
    $literal= literal($type);
    return $object instanceof $literal;
  }
}
// }}}

// {{{ proto string literal(string type)
//     Returns the correct literal
function literal($type) {
  if (isset(\xp::$sn[$type])) {
    return \xp::$sn[$type];
  } else if ('[]' === substr($type, -2)) {
    return "\xa6".literal(substr($type, 0, -2));
  } else if ('[:' === substr($type, 0, 2)) {
    return "\xbb".literal(substr($type, 2, -1));
  } else if (false !== ($p= strpos($type, '<'))) {
    $l= literal(substr($type, 0, $p))."\xb7\xb7";
    for ($args= substr($type, $p+ 1, -1).',', $o= 0, $brackets= 0, $i= 0, $s= strlen($args); $i < $s; $i++) {
      if (',' === $args{$i} && 0 === $brackets) {
        $l.= strtr(literal(ltrim(substr($args, $o, $i- $o)))."\xb8", '\\', "\xa6");
        $o= $i+ 1;
      } else if ('<' === $args{$i}) {
        $brackets++;
      } else if ('>' === $args{$i}) {
        $brackets--;
      }
    }
    return substr($l, 0, -1);
  } else if (0 === strncmp($type, 'php.', 4)) {
    return substr($type, 4);
  } else {
    return strtr($type, '.', '\\');
  }
}
// }}}

// {{{ proto deprecated void delete(&var object)
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

  // Create unique name
  $n= "\xb7".(++$u);
  if (false === strrpos($spec, '.')) {
    $spec= xp::nameOf($spec);
  }

  // Handle generics, PHP types and all others.
  if (strstr($spec, '<')) {
    $class= Type::forName($spec);
    $type= $class->literal();
    $p= strrpos(substr($type, 0, strpos($type, "\xb7\xb7")), "\xb7");
    $generic= xp::$meta[$class->getName()]['class'][DETAIL_GENERIC];
  } else if (0 === strncmp($spec, 'php.', 4)) {
    $type= $spec= substr($spec, 4);
    $p= false;
    $generic= null;
  } else {
    try {
      $type= xp::$loader->loadClass0($spec);
    } catch (\lang\ClassLoadingException $e) {
      xp::error($e->getMessage());
    }
    $p= strrpos($type, "\xb7");
    $generic= null;
  }

  if (false !== $p) {
    $name= strtr(substr($type, 0, $p), "\xb7", '.').'.'.substr($type, $p+ 1).$n;
  } else {
    $name= strtr($type, '\\', '.').$n;
  }

  if (interface_exists($type)) {
    $decl= ['kind' => 'class', 'extends' => ['lang.Object'], 'implements' => ['\\'.$type], 'use' => []];
  } else if (trait_exists($type)) {
    $decl= ['kind' => 'class', 'extends' => ['lang.Object'], 'implements' => [], 'use' => ['\\'.$type]];
  } else {
    $decl= ['kind' => 'class', 'extends' => ['\\'.$type], 'implements' => [], 'use' => []];
  }
  $defined= \lang\ClassLoader::defineType($annotations.$name, $decl, $def);

  if (false === strpos($type, '\\')) {
    xp::$cn[$type.$n]= $spec.$n;
  }

  if ($generic) {
    \lang\XPClass::detailsForClass($name);
    xp::$meta[$name]['class'][DETAIL_GENERIC]= $generic;
  }

  if ($defined->hasConstructor()) {
    return $defined->getConstructor()->newInstance($args);
  } else {
    return $defined->newInstance();
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
    $reflect= $class->newGenericType($typeargs)->reflect();
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

// {{{ main
error_reporting(E_ALL);
set_error_handler('__error');
date_default_timezone_set(ini_get('date.timezone')) || xp::error('[xp::core] date.timezone not configured properly.');

define('MODIFIER_STATIC',       1);
define('MODIFIER_ABSTRACT',     2);
define('MODIFIER_FINAL',        4);
define('MODIFIER_PUBLIC',     256);
define('MODIFIER_PROTECTED',  512);
define('MODIFIER_PRIVATE',   1024);

xp::$null= new null();
xp::$loader= new xp();

// Paths are passed via class loader API from *-main.php. Retaining BC:
// Paths are constructed inside an array before including this file.
if (isset($GLOBALS['paths'])) {
  xp::$classpath= $GLOBALS['paths'];
} else if (0 === strpos(__FILE__, 'xar://')) {
  xp::$classpath= [substr(__FILE__, 6, -14)];
} else {
  xp::$classpath= [__DIR__.DIRECTORY_SEPARATOR];
}
set_include_path(rtrim(implode(PATH_SEPARATOR, xp::$classpath), PATH_SEPARATOR));

spl_autoload_register(function($class) {
  $name= strtr($class, '\\', '.');
  $cl= xp::$loader->findClass($name);
  if (null === $cl) return false;
  $cl->loadClass0($name);
  return true;
});
spl_autoload_register(function($class) {
  if (false === strrpos($class, '\\import')) {
    return false;
  } else {
    class_alias('import', $class);
    return true;
  }
});

foreach ([
  'lang.Generic', 'lang.Object', 'lang.Type', 'lang.XPClass', 'lang.ClassLoader',
  'lang.Throwable', 'lang.XPException', 'lang.Error',
  'lang.types.String', 'lang.types.Bytes', 'lang.types.Integer', 'lang.types.Double', 'lang.types.Boolean',
  'lang.types.ArrayList', 'lang.types.ArrayMap',
  'lang.reflect.Method', 'lang.reflect.Constructor', 'lang.reflect.Field'
] as $class) {
  $short= substr($class, strrpos($class, '.') + 1);
  class_alias(strtr($class, '.', '\\'), $short);
  \xp::$cn[$short]= $class;
}
// }}}