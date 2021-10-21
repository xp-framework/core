<?php namespace util;

use lang\{IllegalArgumentException, ClassLoader};

/**
 * Class path-based property source
 *
 */
class ClassPathPropertySource implements PropertySource {
  protected $root, $loader;

  /**
   * Constructor
   *
   * @param  string $path
   * @param  lang.AbstractClassLoader $loader
   */
  public function __construct($path, $loader= null) {
    $this->root= $path;
    $this->loader= $loader ?? ClassLoader::getDefault();
  }

  /**
   * Check whether source provides given properies
   *
   * @param   string name
   * @return  bool
   */
  public function provides($name) {
    return $this->loader->providesResource($this->root.'/'.$name.'.ini');
  }

  /**
   * Load properties by given name
   *
   * @param   string name
   * @return  util.Properties
   * @throws  lang.IllegalArgumentException if property requested is not available
   */
  public function fetch($name) {
    if (!$this->provides($name)) {
      throw new IllegalArgumentException('No properties '.$name.' found at '.$this->root);
    }

    $p= new Properties();
    $p->load($this->loader->getResourceAsStream($this->root.'/'.$name.'.ini'));
    return $p;
  }

  /**
   * Returns hashcode for this source
   *
   * @return  string
   */
  public function hashCode() {
    return 'CL'.md5($this->root);
  }

  /**
   * Check if this instance equals another
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $cmp->root === $this->root && 0 === $this->loader->compareTo($cmp->loader);
  }

  /**
   * Creates a string representation of this object
   *
   * @return string
   */
  public function toString() {
    return nameof($this).'<'.$this->root.'>';
  }
}
