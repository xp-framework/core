<?php

// {{{ final class xp
final class xp {
  const CLASS_FILE_EXT= '.class.php';
  const ENCODING= 'utf-8';

  public static $cli= [];
  public static $cll= 0;
  public static $cl= [];
  public static $cn= ['xp' => '<xp>'];
  public static $sn= [
    'xp'     => 'xp',
    'string' => "\xfestring",
    'int'    => "\xfeint",
    'float'  => "\xfefloat",
    'double' => "\xfefloat",
    'bool'   => "\xfebool",
    'var'    => "var",
  ];
  public static $loader= null;
  public static $classpath= null;
  public static $errors= [];
  public static $meta= [];
  
  // {{{ proto lang.IClassLoader findClass(string class)
  //     Finds the class loader for a class by its fully qualified name
  function findClass($class) {
    return $this;
  }
  // }}}

  // {{{ proto string loadClass0(string class)
  //     Loads a class by its fully qualified name
  function loadClass0($class) {
    $name= strtr($class, '.', '\\');
    if (isset(xp::$cl[$class])) return $name;
    foreach (xp::$classpath as $path) {

      // We rely on paths having been expanded including a trailing directory separator
      // character inside bootstrap(). This way, we can save testing for whether the path
      // entry is a directory with file system stat() calls.
      if (DIRECTORY_SEPARATOR === $path[strlen($path) - 1]) {
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
      method_exists($name, '__static') && xp::$cli[]= [$name, '__static'];
      if (0 === xp::$cll) {
        $invocations= xp::$cli;
        xp::$cli= [];
        foreach ($invocations as $inv) $inv($name);
      }

      return $name;
    }
    
    throw new \Exception('Cannot bootstrap class '.$class.' (include_path= '.get_include_path().')', 0x3d);
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

// {{{ proto void __error(int code, string msg, string file, int line)
//     Error callback
function __error($code, $msg, $file, $line) {
  if (0 === error_reporting() || null === $file) return;

  if (E_RECOVERABLE_ERROR === $code) {
    if (0 === strncmp($msg, 'Object', 6) || strpos($msg, '__toString() must')) {
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
    0 === strncmp($msg, 'Undefined array', 15) ||
    0 === strncmp($msg, 'Undefined index', 15) ||
    0 === strncmp($msg, 'Uninitialized string', 20)
  )) {
    throw new \lang\IndexOutOfBoundsException($msg);
  } else if ('string conversion' === substr($msg, -17)) {
    throw new \lang\ClassCastException($msg);
  } else {
    $bt= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $class= $bt[1]['class'] ?? null;
    $method= $bt[1]['function'] ?? null;
    
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

// {{{ proto var cast (var arg, var type)
//     Casts an arg NULL-safe
function cast($arg, $type) {
  if (null === $arg) {
    if ('?' === $type[0]) return null;
    throw new \lang\ClassCastException('Cannot cast NULL to '.$type);
  } else if ($type instanceof \lang\Type) {
    return $type->cast($arg);
  } else {
    try {
      return \lang\Type::forName($type)->cast($arg);
    } catch (\lang\Throwable $t) {
      throw new \lang\ClassCastException('Cannot cast', $t);
    }
  }
}
// }}}

// {{{ proto bool is(string type, var object)
//     Checks whether a given object is an instance of the type given
function is($type, $object) {
  if ('int' === $type) {
    return is_int($object);
  } else if ('float' === $type || 'double' === $type) {
    return is_float($object);
  } else if ('string' === $type) {
    return is_string($object);
  } else if ('bool' === $type) {
    return is_bool($object);
  } else if ('var' === $type) {
    return true;
  } else if ('array' === $type) {
    return is_array($object);
  } else if ('object' === $type) {
    return is_object($object);
  } else if ('callable' === $type) {
    return is_callable($object);
  } else if ('iterable' === $type) {
    return is_array($object) || $object instanceof \Traversable;
  } else if ('?' === $type[0]) {
    return null === $object || is(substr($type, 1), $object);
  } else if (0 === strncmp($type, 'function(', 9)) {
    return \lang\FunctionType::forName($type)->isInstance($object);
  } else if (0 === substr_compare($type, '[]', -2)) {
    return (new \lang\ArrayType(substr($type, 0, -2)))->isInstance($object);
  } else if (0 === substr_compare($type, '[:', 0, 2)) {
    return (new \lang\MapType(substr($type, 2, -1)))->isInstance($object);
  } else if (0 === strncmp($type, '(function(', 10)) {
    return \lang\FunctionType::forName(substr($type, 1, -1))->isInstance($object);
  } else if (strstr($type, '|')) {
    return \lang\TypeUnion::forName($type)->isInstance($object);
  } else if (strstr($type, '&')) {
    return \lang\TypeIntersection::forName($type)->isInstance($object);
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
      if (',' === $args[$i] && 0 === $brackets) {
        $l.= strtr(literal(ltrim(substr($args, $o, $i- $o)))."\xb8", '\\', "\xa6");
        $o= $i+ 1;
      } else if ('<' === $args[$i]) {
        $brackets++;
      } else if ('>' === $args[$i]) {
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

// {{{ proto void with(arg..., Closure)
//     Executes closure, closes all given args on exit.
function with(... $args) {
  if (($block= array_pop($args)) instanceof \Closure)  {
    try {
      return $block(...$args);
    } finally {
      foreach ($args as $arg) {
        try {
          if ($arg instanceof \lang\Closeable) {
            $arg->close();
          } else if ($arg instanceof \IDisposable) {
            $arg->__dispose();
          }
        } catch (\Throwable $ignored) {
          // ...
        }
      }
    }
  }
}
// }}}

// {{{ proto object newinstance(string spec, var[] args, var def)
//     Anonymous instance creation
function newinstance($spec, $args, $def= null) {
  static $u= 0;

  if ('#' === $spec[0]) {
    $p= strrpos($spec, ' ');
    $annotations= substr($spec, 0, $p).' ';
    $spec= substr($spec, $p+ 1);
  } else {
    $annotations= '';
  }

  // Create unique name
  $n= "\xb7".(++$u);
  if (0 === strncmp($spec, 'php.', 4)) {
    $spec= substr($spec, 4);
  }

  // Handle generics, PHP types and all others.
  if (strstr($spec, '<')) {
    $class= \lang\Type::forName($spec);
    $type= $class->literal();
    $generic= xp::$meta[$class->getName()]['class'][DETAIL_GENERIC];
  } else if (false === strpos($spec, '.')) {
    $type= $spec;
    $generic= null;
  } else {
    $type= xp::$loader->loadClass0($spec);
    $generic= null;
  }

  $name= strtr($type, '\\', '.').$n;
  if (interface_exists($type)) {
    $decl= ['kind' => 'class', 'extends' => null, 'implements' => ['\\'.$type], 'use' => []];
  } else if (trait_exists($type)) {
    $decl= ['kind' => 'class', 'extends' => null, 'implements' => [], 'use' => ['\\'.$type]];
  } else {
    $decl= ['kind' => 'class', 'extends' => ['\\'.$type], 'implements' => [], 'use' => []];
  }

  // Single-method interface support
  if ($def instanceof \Closure) {
    $methods= (new \ReflectionClass($type))->getMethods(MODIFIER_ABSTRACT);
    if (1 !== sizeof($methods)) {
      throw new \lang\ClassFormatException(strtr($type, '\\', '.').' is not a single-method interface');
    }
    $def= [$methods[0]->getName() => $def];
  }

  $defined= \lang\ClassLoader::defineType($annotations.$name, $decl, $def);

  if (false === strpos($type, '\\')) {
    xp::$cn[$type.$n]= $spec.$n;
  }

  if ($generic) {
    \lang\XPClass::detailsForClass($name);
    xp::$meta[$name]['class'][DETAIL_GENERIC]= $generic;
  }

  return $defined->newInstance(...$args);
}
// }}}

// {{{ proto object create(string spec, var... $args)
//     Creates a generic object
function create($spec, ... $args) {
  if (!is_string($spec)) {
    throw new \lang\IllegalArgumentException('Create expects its first argument to be a string');
  }

  // Parse type specification: "new " TYPE "()"?
  // TYPE:= B "<" ARGS ">"
  // ARGS:= TYPE [ "," TYPE [ "," ... ]]
  $b= strpos($spec, '<');
  $base= substr($spec, 4, $b- 4);
  $typeargs= \lang\Type::forNames(substr($spec, $b+ 1, strrpos($spec, '>')- $b- 1));
  
  // Instantiate, passing the rest of any arguments passed to create()
  // BC: Wrap IllegalStateExceptions into IllegalArgumentExceptions
  $class= \lang\XPClass::forName(strstr($base, '.') ? $base : \lang\XPClass::nameOf($base));
  try {
    return $class->newGenericType($typeargs)->newInstance(...$args);
  } catch (\lang\IllegalStateException $e) {
    throw new \lang\IllegalArgumentException($e->getMessage());
  } catch (ReflectionException $e) {
    throw new \lang\IllegalAccessException($e->getMessage());
  }
}
// }}}

// {{{ proto string nameof(object arg)
//     Returns name of an instance.
function nameof($arg) {
  $class= get_class($arg);
  if (isset(xp::$cn[$class])) {
    return xp::$cn[$class];
  } else if (strstr($class, '\\')) {
    return strtr($class, '\\', '.');
  } else {
    $name= array_search($class, xp::$sn, true);
    return false === $name ? $class : $name;
  }
}
// }}}

// {{{ proto lang.Type typeof(mixed arg)
//     Returns type
function typeof($arg) {
  if (null === $arg) {
    return \lang\Type::$VOID;
  } else if ($arg instanceof \Closure) {
    $r= new \ReflectionFunction($arg);
    $signature= [];
    foreach ($r->getParameters() as $param) {
      if ($param->isVariadic()) break;
      $signature[]= \lang\Type::resolve($param->getType()) ?? \lang\Type::$VAR;
    }
    return new \lang\FunctionType($signature, \lang\Type::resolve($r->getReturnType()) ?? \lang\Type::$VAR);
  } else if (is_object($arg)) {
    $class= get_class($arg);
    if (0 === strncmp($class, 'class@anonymous', 15)) {
      $r= new \ReflectionObject($arg);
      \xp::$cl[strtr($class, '\\', '.')]= 'lang.AnonymousClassLoader://'.$r->getStartLine().':'.$r->getEndLine().'@'.$r->getFileName();
      return new \lang\XPClass($r);
    }
    return new \lang\XPClass($class);
  } else if (is_array($arg)) {
    return 0 === key($arg) ? \lang\ArrayType::forName('var[]') : \lang\MapType::forName('[:var]');
  } else {
    return \lang\Type::forName(gettype($arg));
  }
}
// }}}

// {{{ interface IDisposable
if (!interface_exists(\IDisposable::class, false)) {
  eval('interface IDisposable { public function __dispose(); }');
}
// }}}

// {{{ PHP 8.1 enums
if (!function_exists('enum_exists')) {
  interface UnitEnum { }
  interface BackedEnum extends UnitEnum { }

  function enum_exists($name, $load= true) {
    return class_exists($name, $load) && $name instanceof \UnitEnum;
  }
}
// }}}

// {{{ main
error_reporting(E_ALL);
set_error_handler('__error');

// If iconv is not available but mbstring is, create snap-in replacements
if (!defined('ICONV_IMPL')) {
  function iconv($from, $to, $str) {
    return mb_convert_encoding($str, $to ?: 'utf-8', $from ?: 'utf-8');
  }

  function iconv_strlen($str, $charset) {
    return mb_strlen($str, $charset ?: 'utf-8');
  }
}

define('MODIFIER_STATIC',    \ReflectionMethod::IS_STATIC);
define('MODIFIER_ABSTRACT',  \ReflectionMethod::IS_ABSTRACT);
define('MODIFIER_FINAL',     \ReflectionMethod::IS_FINAL);
define('MODIFIER_PUBLIC',    \ReflectionMethod::IS_PUBLIC);
define('MODIFIER_PROTECTED', \ReflectionMethod::IS_PROTECTED);
define('MODIFIER_PRIVATE',   \ReflectionMethod::IS_PRIVATE);
define('MODIFIER_READONLY',  128); // PHP 8.1: \ReflectionProperty::IS_READONLY

define('DETAIL_ARGUMENTS',   1);
define('DETAIL_RETURNS',     2);
define('DETAIL_THROWS',      3);
define('DETAIL_COMMENT',     4);
define('DETAIL_ANNOTATIONS', 5);
define('DETAIL_TARGET_ANNO', 6);
define('DETAIL_GENERIC',     7);

defined('T_FN') || define('T_FN', -346);
defined('T_ATTRIBUTE') || define('T_ATTRIBUTE', -383);
defined('T_NAME_FULLY_QUALIFIED') || define('T_NAME_FULLY_QUALIFIED', -312);
defined('T_NAME_QUALIFIED') || define('T_NAME_QUALIFIED', -314);
defined('T_ENUM') || define('T_ENUM', -369);

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
// }}}