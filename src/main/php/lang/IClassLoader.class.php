<?php namespace lang;

/**
 * Classloader interface
 */
interface IClassLoader extends Value {

  /**
   * Checks whether this loader can provide the requested class
   *
   * @param  string $class
   * @return bool
   */
  public function providesClass($class);
  
  /**
   * Checks whether this loader can provide the requested resource
   *
   * @param  string $filename
   * @return bool
   */
  public function providesResource($filename);

  /**
   * Checks whether this loader can provide the requested package
   *
   * @param  string $package
   * @return bool
   */
  public function providesPackage($package);

  /**
   * Get package contents
   *
   * @param  ?string $package
   * @return string[]
   */
  public function packageContents($package);

  /**
   * Load the class by the specified name
   *
   * @param  string $class fully qualified class name
   * @return lang.XPClass
   * @throws lang.ClassNotFoundException in case the class can not be found
   */
  public function loadClass($class);

  /**
   * Load the class by the specified name and return its name
   *
   * @param  string $class fully qualified class name
   * @return string
   * @throws lang.ClassNotFoundException in case the class can not be found
   */
  public function loadClass0($class);
  
  /**
   * Loads a resource.
   *
   * @param  string $name name of resource
   * @return string
   * @throws lang.ElementNotFoundException in case the resource cannot be found
   */
  public function getResource($name);
  
  /**
   * Retrieve a stream to the resource
   *
   * @param  string $name name of resource
   * @return io.File
   * @throws lang.ElementNotFoundException in case the resource cannot be found
   */
  public function getResourceAsStream($name);

  /**
   * Returns a unique identifier for this class loader instance
   *
   * @return string
   */
  public function instanceId();

}
