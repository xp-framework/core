<?php namespace lang\codedom;

/**
 * Base class for syntaxes. Subclasses initialize the "parse" member
 * in their static initializer with `self::$parse[__CLASS__]= ...`.
 *
 * @see   xp://lang.codedom.PhpSyntax
 */
abstract class Syntax extends \lang\Object {
  protected static $parse= [];

  /**
   * Parses input
   *
   * @param  string $input
   * @return lang.codedom.CodeUnit
   * @throws lang.FormatException
   */
  public function parse($input) {
    $rules= self::$parse[get_class($this)];
    return $rules[':start']->evaluate($rules, new Stream($input));
  }
}