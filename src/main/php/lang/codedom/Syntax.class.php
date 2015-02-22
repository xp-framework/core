<?php namespace lang\codedom;

/**
 * Base class for syntaxes. Subclasses initialize the "parse" member
 * in their static initializer.
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
    return self::$parse[':start']->evaluate(self::$parse, new Stream($input));
  }
}