<?php namespace lang\codedom;

abstract class TypeDeclaration extends \lang\Object { use Decorations;
  protected $name, $body;

  /**
   * Creates a new type
   *
   * @param  int $modifiers
   * @param  string $annotations
   * @param  string $name
   * @param  lang.codedom.TypeBody $body
   */
  public function __construct($modifiers, $annotations, $name, $body) {
    $this->modifiers= $modifiers;
    $this->annotations= $annotations;
    $this->name= $name;
    $this->body= $body;
  }

  /** @return string */
  public function name() { return $this->name; }

  /** @return lang.codedom.TypeBody */
  public function body() { return $this->body; }

  /** @return bool */
  public function isClass() { return false; }

  /** @return bool */
  public function isInterface() { return false; }

  /** @return bool */
  public function isTrait() { return false; }

}