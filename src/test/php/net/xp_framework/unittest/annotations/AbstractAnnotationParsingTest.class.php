<?php namespace net\xp_framework\unittest\annotations;

use lang\reflect\ClassParser;
use unittest\Assert;

/**
 * Base class for parent access tests
 */
abstract class AbstractAnnotationParsingTest {
  const PARENTS_CONSTANT = 'constant';
  public static $parentsExposed = 'exposed';
  protected static $parentsHidden = 'hidden';
  private static $parentsInternal = 'internal';

  /**
   * Helper
   *
   * @param   string input
   * @return  [:var]
   */
  protected function parse($input) {
    return (new ClassParser())->parseAnnotations($input, nameof($this), [
      'Namespaced' => 'net.xp_framework.unittest.annotations.fixture.Namespaced'
    ]);
  }
}