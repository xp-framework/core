<?php namespace xp;

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  trigger_error('This version of the XP Framework requires PHP 5.4.0+, have PHP '.PHP_VERSION, E_USER_ERROR);
  exit(0x3d);
}

require __DIR__.DIRECTORY_SEPARATOR.'lang.base.php';

\lang\ClassLoader::registerPath(__DIR__);