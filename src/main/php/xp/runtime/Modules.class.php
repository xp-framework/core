<?php namespace xp\runtime;

use lang\reflect\Module;
use lang\{Environment, ElementNotFoundException, FormatException};

/** @test xp://net.xp_framework.unittest.runtime.ModulesTest */
class Modules {
  private $list= [];
  protected $vendorDir= null, $userDir= null;
  
  /**
   * Adds a module
   *
   * @param  string $module
   * @param  ?string $version
   * @return self
   */
  public function add($module, $version= null) {
    $this->list[$module]= $version;
    return $this;
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
   * @return self
   * @throws xp.runtime.CouldNotLoadDependencies
   */
  public function require($namespace) {
    $failed= $loaders= [];
    foreach ($this->list as $module => $version) {
      foreach ([$this->userDir($namespace), $this->vendorDir()] as $dir) {
        if (is_dir(strtr("{$dir}/vendor/{$module}", '/', DIRECTORY_SEPARATOR))) {
          $loaders[$dir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php']= true;
          continue 2;
        }
      }
      $failed[]= $module;
    }
    if ($failed) throw new CouldNotLoadDependencies($failed);

    foreach ($loaders as $file => $_) {
      require_once $file;
    }
    return $this;
  }
}