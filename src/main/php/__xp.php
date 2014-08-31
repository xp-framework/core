<?php
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  trigger_error('This version of the XP Framework requires PHP 5.4.0+, have PHP '.PHP_VERSION, E_USER_ERROR);
  exit(0x3d);
}
require __DIR__.DIRECTORY_SEPARATOR.'lang.base.php';

date_default_timezone_set(ini_get('date.timezone')) || xp::error('[xp::core] date.timezone not configured properly.');

define('LONG_MAX', PHP_INT_MAX);
define('LONG_MIN', -PHP_INT_MAX - 1);
define('MODIFIER_STATIC',       1);
define('MODIFIER_ABSTRACT',     2);
define('MODIFIER_FINAL',        4);
define('MODIFIER_PUBLIC',     256);
define('MODIFIER_PROTECTED',  512);
define('MODIFIER_PRIVATE',   1024);

error_reporting(E_ALL);
ini_set('display_errors', 'false');
set_error_handler('__error');

global $paths;
if (!isset($paths)) $paths= array(__DIR__.DIRECTORY_SEPARATOR, '.'.DIRECTORY_SEPARATOR);
xp::$null= new null();
xp::$loader= new xp();
xp::$classpath= $paths;
set_include_path(rtrim(implode(PATH_SEPARATOR, $paths), PATH_SEPARATOR));

spl_autoload_register(function($class) {
  $name= strtr($class, '\\', '.');
  $cl= xp::$loader->findClass($name);
  if ($cl instanceof null) return false;
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