<?php namespace lang;

/**
 * Indicates a class specified by a name cannot be found - that is,
 * no classloader provides such a class.
 *
 * @see   lang.IClassLoader#loadClass
 * @see   lang.XPClass#forName
 * @test  net.xp_framework.unittest.reflection.ClassLoaderTest
 */
class ClassDependencyException extends ClassNotFoundException {

  /**
   * Returns the exception's message - override this in
   * subclasses to provide exact error hints.
   *
   * @return  string
   */
  protected function message() {
    return 'Dependencies for class "%s" could not be loaded';
  }
}
