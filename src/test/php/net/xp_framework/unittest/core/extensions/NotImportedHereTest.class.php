<?php namespace net\xp_framework\unittest\core\extensions;

use lang\Error;
use lang\types\ArrayList;

/**
 * Tests situation when ArrayListExtensions hasn't been imported here
 * but inside another class which is imported here.
 *
 * @see   xp://net.xp_framework.unittest.core.extensions.ArrayListExtensions
 * @see   xp://net.xp_framework.unittest.core.extensions.ArrayListDemo
 */
class NotImportedHereTest extends \unittest\TestCase {

  #[@test, @expect(Error::class)]
  public function test() {
    (new ArrayList(7, 0, 10, 1, -1))->sorted();
  }
}
