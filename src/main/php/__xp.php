<?php namespace xp;

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  trigger_error('This version of the XP Framework requires PHP 5.4.0+, have PHP '.PHP_VERSION, E_USER_ERROR);
  exit(0x3d);
}

$p= max(strrpos(__FILE__, DIRECTORY_SEPARATOR), strrpos(__FILE__, '?'));
require_once substr(__FILE__, 0, $p + 1).'lang.base.php';

\lang\ClassLoader::getDefault();