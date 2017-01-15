<?php namespace xp\runtime;

use lang\{Error, Environment};
use lang\reflect\Module;

class Modules {
  private $list= [];
  private $loaded= ['php' => true, 'xp-framework/core' => true];
  private $vendorDir= null;
  
  /**
   * Adds a module
   *
   * @param  string $module
   * @return void
   */
  public function add($module) {
    $this->list[]= $module;
  }

  /**
   * Returns list of all modules
   *
   * @return string[]
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
   * Requires modules
   *
   * @return void
   * @throws lang.Error
   */
  public function require() {
    foreach ($this->list as $module) {
      $this->load($module);
    }
  }

  /**
   * Loads a single module
   *
   * @param  string $module
   * @return void
   * @throws lang.Error
   */
  public function load($module) {
    if (!isset($this->loaded[$module]) && !Module::loaded($module)) {
      $base= $this->vendorDir().strtr($module, '/', DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
      if (null === ($defines= json_decode(file_get_contents($base.'composer.json'), true))) {
        throw new Error('Could not load module '.$module);
      }

      $this->loaded[$module]= true;
      foreach ($defines['autoload']['files'] as $file) {
        require($base.strtr($file, '/', DIRECTORY_SEPARATOR));
      }
      foreach ($defines['require'] as $depend => $_) {
        $this->load($depend);
      }
    }
  }
}