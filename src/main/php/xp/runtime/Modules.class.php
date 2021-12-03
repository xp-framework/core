<?php namespace xp\runtime;

use lang\reflect\Module;
use lang\{Environment, ElementNotFoundException, FormatException};

/** @test xp://net.xp_framework.unittest.runtime.ModulesTest */
class Modules {
  private $list= [];
  private $loaded= ['php' => true, 'xp-framework/core' => true];
  protected $vendorDir= null, $userDir= null;
  
  /**
   * Adds a module
   *
   * @param  string $module
   * @param  ?string $version
   * @return void
   */
  public function add($module, $version= null) {
    $this->list[$module]= $version;
  }

  /**
   * Returns map of all modules and their associated imports
   *
   * @return [:string]
   */
  public function all() { return $this->list; }

  /**
   * Returns version of a given module
   *
   * @param  string $module
   * @return ?string
   * @throws lang.ElementNotFoundException
   */
  public function version($module) {
    if (array_key_exists($module, $this->list)) return $this->list[$module];

    throw new ElementNotFoundException('No such module "'.$module.'"');
  }

  /**
   * Returns composer vendor directory relevant for loading module
   *
   * @see    https://getcomposer.org/doc/03-cli.md#composer-home
   * @return string
   */
  public function vendorDir() {
    if (null === $this->vendorDir) {
      $this->vendorDir= getcwd();
      if (!is_dir($this->vendorDir.DIRECTORY_SEPARATOR.'vendor')) {
        $this->vendorDir= getenv('COMPOSER_HOME') ?: Environment::configDir('composer', false);
      }
    }
    return $this->vendorDir;
  }

  /**
   * Returns namespaced user vendor directory relevant for loading module
   *
   * @param  string $namespace
   * @return string
   */
  public function userDir($namespace) {
    if (null === $this->userDir) {
      $this->userDir= Environment::configDir('xp');
    }
    return $this->userDir.strtr($namespace, ['\\' => DIRECTORY_SEPARATOR]);
  }

  /**
   * Requires modules in a given namespace
   *
   * @param  string $namespace
   * @return void
   * @throws xp.runtime.CouldNotLoadDependencies
   */
  public function require($namespace) {
    $errors= [];
    foreach ($this->list as $module => $version) {
      if ($e= $this->load($namespace, $module, $version)) $errors[$module]= $e;
    }
    if ($errors) {
      throw new CouldNotLoadDependencies($errors);
    }
  }

  /**
   * Loads a single module from a given namespace
   *
   * @param  string $namespace
   * @param  string $module
   * @param  ?string $version
   * @return ?lang.XPException
   */
  private function load($namespace, $module, $version= null) {
    if (isset($this->loaded[$module]) || Module::loaded($module)) return null;

    // PHP extensions...
    if (0 === strncmp($module, 'ext-', 4)) {
      $extension= substr($module, 4);
      return extension_loaded($extension) ? null : new ExtensionNotLoaded($extension);
    }

    // ...vs. userland modules
    foreach ([$this->vendorDir(), $this->userDir($namespace)] as $dir) {
      $base= (
        $dir.DIRECTORY_SEPARATOR
        .'vendor'.DIRECTORY_SEPARATOR
        .strtr($module, '/', DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
      );
      if (!is_dir($base)) continue;

      $defines= json_decode(file_get_contents($base.'composer.json'), true);
      if (!is_array($defines)) {
        return new FormatException($base.'composer.json');
      }

      $this->loaded[$module]= true;
      foreach ($defines['autoload']['files'] ?? [] as $file) {
        require($base.strtr($file, '/', DIRECTORY_SEPARATOR));
      }

      // See https://www.php-fig.org/psr/psr-0/: Underscores special case, e.g.
      // name\space\Class_Name => name/space/Class/Name.php
      foreach ($defines['autoload']['psr-0'] ?? [] as $prefix => $source) {
        $path= $base.rtrim(strtr($source, '/', DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        spl_autoload_register(function($class) use($prefix, $path) {
          if (0 !== strncmp($class, $prefix, strlen($prefix))) return false;

          if ($p= strrpos($class, '\\')) {
            $path.= strtr(substr($class, 0, $p + 1), '\\', DIRECTORY_SEPARATOR);
            $class= substr($class, $p + 1);
          }

          require $path.strtr($class, '_', DIRECTORY_SEPARATOR).'.php';
        });
      }

      // See https://www.php-fig.org/psr/psr-4/: Prefix is stripped, e.g.
      // name\space\Class_Name with prefix "name" => space/Class_Name.php
      foreach ($defines['autoload']['psr-4'] ?? [] as $prefix => $source) {
        $path= $base.rtrim(strtr($source, '/', DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        spl_autoload_register(function($class) use($prefix, $path) {
          $l= strlen($prefix);
          if (0 !== strncmp($class, $prefix, $l)) return false;

          require $path.substr(strtr($class, '\\', DIRECTORY_SEPARATOR), $l).'.php';
        });
      }

      $errors= [];
      foreach ($defines['require'] ?? [] as $dependency => $version) {
        if ($e= $this->load($namespace, $dependency, $version)) $errors[$dependency]= $e;
      }
      return $errors ? new CouldNotLoadDependencies($errors) : null;
    }

    return new ModuleNotFound($module);
  }
}