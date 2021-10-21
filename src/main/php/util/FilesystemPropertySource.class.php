<?php namespace util;

use lang\IllegalArgumentException;

/**
 * Filesystem-based property source
 *
 * @test  net.xp_framework.unittest.util.FilesystemPropertySourceTest
 */
class FilesystemPropertySource implements PropertySource {
  protected $root;

  /**
   * Constructor
   *
   * @param   string path
   */
  public function __construct($path) {
    $this->root= realpath($path);
  }

  /**
   * Check whether source provides given properies
   *
   * @param   string name
   * @return  bool
   */
  public function provides($name) {
    return file_exists($this->root.DIRECTORY_SEPARATOR.$name.'.ini');
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

    return new Properties($this->root.DIRECTORY_SEPARATOR.$name.'.ini');
  }

  /**
   * Returns hashcode for this source
   *
   * @return  string
   */
  public function hashCode() {
    return 'FS'.md5($this->root);
  }

  /**
   * Check if this instance equals another
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $cmp->root === $this->root;
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
