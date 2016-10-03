<?php namespace xp\runtime;

use lang\{Error, Environment};
use lang\reflect\Package;

abstract class Imports {
  const FROM_FORWARD= 'function from($module, $imports= [], $namespace= __NAMESPACE__, $composer= null) { \xp\runtime\Imports::from($module, $imports, $namespace, $composer);
  }';

  /**
   * Import from
   *
   * @param  string $module
   * @param  string|string[] $imports
   * @param  string $namespace
   * @param  string $composer
   */
  public static function from($module, $imports, $namespace, $composer) {
    static $modules= ['php' => true, 'xp-framework/core' => true];

    if (!isset($modules[$module])) {

      // Composer local or global (see https://getcomposer.org/doc/03-cli.md#composer-home)
      if (null === $composer) {
        if (is_dir('vendor')) {
          $composer= '.';
        } else {
          $composer= getenv('COMPOSER_HOME') ?: Environment::configDir('composer', false);
        }
      }

      $base= $composer.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.strtr($module, '/', DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
      if (null === ($defines= json_decode(file_get_contents($base.'composer.json'), true))) {
        throw new Error('Could not load module '.$module.' @ '.$composer);
      }

      $modules[$module]= true;
      foreach ($defines['autoload']['files'] as $file) {
        require($base.strtr($file, '/', DIRECTORY_SEPARATOR));
      }
      foreach ($defines['require'] as $depend => $_) {
        from($depend, [], $namespace, $composer);
      }
    }

    foreach ((array)$imports as $import) {
      if ('*' === $import{strlen($import)- 1}) {
        $package= Package::forName(strtr(substr($import, 0, -2), '\\', '.'));
        foreach ($package->getClassNames() as $name) {
          class_alias(strtr($name, '.', '\\'), $namespace.'\\'.substr($name, strrpos($name, '.')+ 1));
        }
      } else if ($p= strpos($import, '{')) {
        $base= substr($import, 0, $p);
        foreach (explode(', ', substr($import, $p + 1, -1)) as $type) {
          if (!class_alias($base.$type, $namespace.'\\'.$type)) {
            throw new Error('Cannot import '.$base.$type);
          }
        }
      } else {
        if (!class_alias($import, $namespace.substr($import, strrpos($import, '\\')))) {
          throw new Error('Cannot import '.$import);
        }
      }
    }
  }
}