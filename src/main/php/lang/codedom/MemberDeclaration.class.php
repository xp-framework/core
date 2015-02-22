<?php namespace lang\codedom;

abstract class MemberDeclaration extends BodyPart { use Decorations;
  protected $name;

  /**
   * Creates a new member declaration
   *
   * @param  int $modifiers
   * @param  string $annotations
   * @param  string $name
   */
  public function __construct($modifiers, $annotations, $name) {
    $this->modifiers= $modifiers;
    $this->annotations= $annotations;
    $this->name= $name;
  }

  /** @return string */
  public function type() { return 'member'; }

  /** @return string */
  public function name() { return $this->name; }

  /** @return bool */
  public function isConstant() { return false; }

  /** @return bool */
  public function isField() { return false; }

  /** @return bool */
  public function isMethod() { return false; }
}