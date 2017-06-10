<?php namespace xp;

if (PHP_VERSION_ID < 70000) {
  throw new \Exception('This version of the XP Framework requires PHP 7.0.0+, have PHP '.PHP_VERSION);
}
if (defined('HHVM_VERSION_ID')) {
  if (HHVM_VERSION_ID < 32000) {
    throw new \Exception('This version of the XP Framework requires HHVM 3.20+, have HHVM '.HHVM_VERSION);
  } else if (!ini_get('hhvm.php7.all')) {
    throw new \Exception('This version of the XP Framework requires hhvm.php7.all to be set to 1');
  }
}

$p= max(strrpos(__FILE__, DIRECTORY_SEPARATOR), strrpos(__FILE__, '?'));
require_once substr(__FILE__, 0, $p + 1).'lang.base.php';

\lang\ClassLoader::getDefault();