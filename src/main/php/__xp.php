<?php namespace xp;

if (PHP_VERSION_ID < 70400) {
  throw new \Exception('This version of the XP Framework requires PHP 7.4.0+, have PHP '.PHP_VERSION);
}

$p= max(strrpos(__FILE__, DIRECTORY_SEPARATOR), strrpos(__FILE__, '?'));
require_once substr(__FILE__, 0, $p + 1).'lang.base.php';

\lang\ClassLoader::getDefault();