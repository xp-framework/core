<?php namespace lang\codedom;

/**
 * Base class for all parts of a type body: Constants, fields, methods
 * and trait use statements.
 */
abstract class BodyPart extends \lang\Object {

  /** @return string */
  public abstract function type();
}