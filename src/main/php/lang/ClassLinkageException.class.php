<?php namespace lang;

/**
 * Indicates a class specified by a name cannot be found - that is,
 * no classloader provides such a class.
 *
 * @see   lang.IClassLoader#loadClass
 * @see   lang.XPClass#forName
 * @test  lang.unittest.ClassLoaderTest
 */
class ClassLinkageException extends ClassNotFoundException {

  /**
   * Returns the exception's message - override this in
   * subclasses to provide exact error hints.
   *
   * @return  string
   */
  protected function message() {
    return 'Class definition for "%s" is not complete';
  }
}
