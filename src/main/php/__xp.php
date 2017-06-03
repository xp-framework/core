<?php namespace xp;

if (version_compare(PHP_VERSION, '7.0.0', '<')) {
  throw new \Exception('This version of the XP Framework requires PHP 7.0.0+, have PHP '.PHP_VERSION);
}

$p= max(strrpos(__FILE__, DIRECTORY_SEPARATOR), strrpos(__FILE__, '?'));
require_once substr(__FILE__, 0, $p + 1).'lang.base.php';

\lang\ClassLoader::getDefault();