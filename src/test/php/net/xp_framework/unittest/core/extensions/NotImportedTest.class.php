<?php namespace net\xp_framework\unittest\core\extensions;

use lang\types\ArrayList;
use lang\Error;

/**
 * Tests situation when ArrayListExtensions hasn't been imported
 *
 * @see   xp://net.xp_framework.unittest.core.extensions.ArrayListExtensions
 */
class NotImportedTest extends \unittest\TestCase {

  #[@test, @expect(Error::class)]
  public function test() {
    (new ArrayList(7, 0, 10, 1, -1))->sorted();
  }
}
