<?php namespace xp;

use lang\ClassLoader;
use lang\reflect\Module;

$module= new Module('xp-framework/core', $cl ?? ClassLoader::registerPath(__DIR__));
