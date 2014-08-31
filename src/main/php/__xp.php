<?php
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  trigger_error('This version of the XP Framework requires PHP 5.4.0+, have PHP '.PHP_VERSION, E_USER_ERROR);
  exit(0x3d);
}
require __DIR__.DIRECTORY_SEPARATOR.'lang.base.php';

error_reporting(E_ALL);

date_default_timezone_set(ini_get('date.timezone')) || xp::error('[xp::core] date.timezone not configured properly.');

define('LONG_MAX', PHP_INT_MAX);
define('LONG_MIN', -PHP_INT_MAX - 1);

spl_autoload_register('__load');
spl_autoload_register('__import');
set_error_handler('__error');
ini_set('display_errors', 'false');

global $paths;
if (!isset($paths)) $paths= array(__DIR__.DIRECTORY_SEPARATOR, '.'.DIRECTORY_SEPARATOR);
xp::$null= new null();
xp::$loader= new xp();
xp::$classpath= $paths;
set_include_path(rtrim(implode(PATH_SEPARATOR, $paths), PATH_SEPARATOR));
