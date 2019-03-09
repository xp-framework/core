<?php namespace xp\runtime;

use lang\Environment;
use lang\reflect\Module;

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
    return $this->userDir.DIRECTORY_SEPARATOR.$namespace;
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

    foreach ([$this->vendorDir(), $this->userDir($namespace)] as $dir) {
      $base= (
        $dir.DIRECTORY_SEPARATOR
        .'vendor'.DIRECTORY_SEPARATOR
        .strtr($module, '/', DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
      );
      if (!is_dir($base)) continue;

      $defines= json_decode(file_get_contents($base.'composer.json'), true);
      $this->loaded[$module]= true;
      foreach ($defines['autoload']['files'] ?? [] as $file) {
        require($base.strtr($file, '/', DIRECTORY_SEPARATOR));
      }

      $errors= [];
      foreach ($defines['require'] as $dependency => $version) {
        if ($e= $this->load($namespace, $dependency, $version)) $errors[$dependency]= $e;
      }
      return $errors ? new CouldNotLoadDependencies($errors) : null;
    }

    return new ModuleNotFound($module);
  }
}