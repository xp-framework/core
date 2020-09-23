<?php namespace net\xp_framework\unittest\core\generics;

use lang\Generic;

/**
 * Generic array filter
 *
 * ```php
 * // Set up filter as anonymous class
 * $webmethods= create('new ArrayFilter<Method>', [], '{
 *   protected function accept($method) {
 *     return $method->hasAnnotation("webmethod");
 *   }
 * }');
 *
 * // Iterate over filtered array
 * foreach ($webmethods->filter($class->getMethods()) as $method) {
 *   Console::writeLine("Webmethod: ", $method);
 * }
 * ```
 */
#[Generic(['self' => 'T'])]
abstract class ArrayFilter {
  
  /**
   * Accept method - called for each element in the specified list.
   * Return TRUE if the passed element should be included in the
   * filtered list, FALSE otherwise
   *
   * @param   T element
   * @return  bool
   */
  #[Generic(['params' => 'T'])]
  protected abstract function accept($element);

  /**
   * Filter a list of elements
   *
   * @param   T[] elements
   * @return  T[] filtered
   */
  #[Generic(['params' => 'T[]', 'return' => 'T[]'])]
  public function filter($elements) {
    $filtered= [];
    foreach ($elements as $element) {
      if ($this->accept($element)) $filtered[]= $element;
    }
    return $filtered;
  }
}