<?php namespace xp\runtime;

use lang\Environment;
use lang\reflect\Module;

class Modules {
  private $list= [];
  private $loaded= ['php' => true, 'xp-framework/core' => true];
  private $vendorDir= null, $userDir= null;
  
  /**
   * Adds a module
   *
   * @param  string $module
   * @param  string $import
   * @return void
   */
  public function add($module, $import= null) {
    $this->list[$module]= $import;
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
      $this->userDir= Environment::configDir('xp').DIRECTORY_SEPARATOR.$namespace;
    }
    return $this->userDir;
  }

  /**
   * Requires modules in a given namespace
   *
   * @param  string $namespace
   * @return void
   * @throws xp.runtime.ModuleNotFound
   */
  public function require($namespace) {
    foreach ($this->list as $module => $import) {
      if (!$this->load($namespace, $module)) {
        throw new ModuleNotFound($module, $import);
      }
    }
  }

  /**
   * Loads a single module from a given namespace
   *
   * @param  string $namespace
   * @param  string $module
   * @return bool
   */
  public function load($namespace, $module) {
    if (isset($this->loaded[$module]) || Module::loaded($module)) return true;

    foreach ([$this->vendorDir(), $this->userDir($namespace)] as $dir) {
      $base= (
        $dir.DIRECTORY_SEPARATOR
        .'vendor'.DIRECTORY_SEPARATOR
        .strtr($module, '/', DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
      );
      if (!is_dir($base)) continue;

      $defines= json_decode(file_get_contents($base.'composer.json'), true);
      $this->loaded[$module]= true;
      foreach ($defines['autoload']['files'] as $file) {
        require($base.strtr($file, '/', DIRECTORY_SEPARATOR));
      }
      foreach ($defines['require'] as $depend => $_) {
        $this->load($namespace, $depend);
      }
      return true;
    }

    return false;
  }
}