<?php namespace lang;

/**
 * Indicates a class specified by a name cannot be found - that is,
 * no classloader provides such a class.
 *
 * @see   lang.IClassLoader#loadClass
 * @see   lang.XPClass#forName
 * @test  lang.unittest.ClassLoaderTest
 * @test  lang.unittest.ReflectionTest
 * @test  lang.unittest.RuntimeClassDefinitionTest
 */
class ClassNotFoundException extends XPException implements ClassLoadingException {
  protected $failedClass= null;
  protected $loaders= [];

  /**
   * Constructor
   *
   * @param  string $failedClass
   * @param  lang.IClassLoader[] $loaders default []
   * @param  lang.Throwable $cause default NULL
   */
  public function __construct($failedClass, $loaders= [], $cause= null) {
    parent::__construct(sprintf($this->message(), $failedClass).($cause ? ': '.$cause->getMessage() : ''), $cause);
    $this->failedClass= $failedClass;
    $this->loaders= $loaders;
  }
  
  /**
   * Returns the classloaders that were asked
   *
   * @return lang.IClassLoader[]
   */
  public function getLoaders() { return $this->loaders; }

  /**
   * Returns the exception's message - override this in
   * subclasses to provide exact error hints.
   *
   * @return string
   */
  protected function message() { return 'Class "%s" could not be found'; }

  /**
   * Retrieve name of class which could not be loaded
   *
   * @return string
   */
  public function getFailedClassName() { return $this->failedClass; }

  /**
   * Retrieve compound representation
   *
   * @return string
   */
  public function compoundMessage() {
    $s= 'Exception '.nameof($this).' ('.$this->message.") {\n";
    foreach ($this->loaders as $loader) {
      $s.= '  '.str_replace("\n", "\n    ", $loader->toString())."\n";
    }
    return $s.'}';
  }
}
