<?php namespace lang\unittest;

use lang\reflect\ClassParser;

abstract class AbstractAnnotationParsingTest {
  const PARENTS_CONSTANT= 'constant';

  public static $parentsExposed= 'exposed';
  protected static $parentsHidden= 'hidden';
  private static $parentsInternal= 'internal';

  /**
   * Helper
   *
   * @param  string $input
   * @return [:var]
   */
  protected function parse($input) {
    return (new ClassParser())->parseAnnotations($input, nameof($this), [
      'Namespaced' => 'lang.unittest.fixture.Namespaced'
    ]);
  }
}