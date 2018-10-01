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
      $composer= getcwd();
      if (!is_dir($composer.DIRECTORY_SEPARATOR.'vendor')) {
        $composer= getenv('COMPOSER_HOME') ?: Environment::configDir('composer', false);
      }
      $this->vendorDir= $composer.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR;
    }
    return $this->vendorDir;
  }

  /**
   * Returns user vendor directory relevant for loading module
   *
   * @return string
   */
  public function userDir() {
    if (null === $this->userDir) {
      $this->userDir= Environment::configDir('xp').DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR;
    }
    return $this->userDir;
  }

  /**
   * Requires modules
   *
   * @return void
   * @throws xp.runtime.ModuleNotFound
   */
  public function require() {
    foreach ($this->list as $module => $import) {
      if (!$this->load($module)) {
        throw new ModuleNotFound($module, $import);
      }
    }
  }

  /**
   * Loads a single module
   *
   * @param  string $module
   * @return bool
   */
  public function load($module) {
    if (isset($this->loaded[$module]) || Module::loaded($module)) return true;

    foreach ([$this->vendorDir(), $this->userDir()] as $dir) {
      $base= $dir.strtr($module, '/', DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
      if (!is_dir($base)) continue;

      $defines= json_decode(file_get_contents($base.'composer.json'), true);
      $this->loaded[$module]= true;
      foreach ($defines['autoload']['files'] as $file) {
        require($base.strtr($file, '/', DIRECTORY_SEPARATOR));
      }
      foreach ($defines['require'] as $depend => $_) {
        $this->load($depend);
      }
      return true;
    }

    return false;
  }
}