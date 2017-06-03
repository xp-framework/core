<?php namespace net\xp_framework\unittest\reflection;

use lang\IClassLoader;
use lang\ElementNotFoundException;
use lang\MethodNotImplementedException;

/**
 * A class loader dummy providing elements supplied to its constructor.
 *
 * @see   xp://net.xp_framework.unittest.reflection.ModuleTest
 */
class LoaderProviding implements IClassLoader {
  protected $resources;

  /**
   * Creates a new loader providing the supplied resources
   *
   * @param  [:string] $resources
   */
  public function __construct(array $resources) {
    $this->resources= $resources;
  }

  /** Creates a string representation */
  public function toString(): string { return nameof($this); }

  /** Returns a hashcode for this class loader */
  public function hashCode(): string { return 'cl@providing'; }

  /** Compares this class loader to another value */
  public function compareTo($value): int { return $value instanceof self ? $value->resources <=> $this->resources : 1; }

  /** @return string */
  public function instanceId() { return 'providing://'.$this->hashCode(); }

  /** @return bool */
  public function providesClass($name) { return false; }

  /** @return bool */
  public function providesPackage($name) { return false; }

  /** @return bool */
  public function providesResource($name) { return isset($this->resources[$name]); }

  /** @return string[] */
  public function packageContents($name) { return array_keys($this->resources); }

  /** @return lang.XPClass */
  public function loadClass($name) { /* Not implemented */ }

  /** @return string */
  public function loadClass0($name) { /* Not implemented */ }

  /** @return string */
  public function getResource($name) {
    if (isset($this->resources[$name])) {
      return $this->resources[$name];
    } else {
      throw new ElementNotFoundException($name);
    }
  }

  /** @return io.Stream */
  public function getResourceAsStream($name) {
    throw new MethodNotImplementedException(__METHOD__);
  }
}