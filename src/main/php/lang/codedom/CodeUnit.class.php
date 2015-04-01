<?php namespace lang\codedom;

use util\Objects;

class CodeUnit extends \lang\Object {
  private $package, $imports, $declaration;

  /**
   * Creates a string representation
   *
   * @param string $package
   * @param string[] $import
   * @param lang.codedom.TypeDeclaration $declaration
   */
  public function __construct($package, $imports, $declaration) {
    $this->package= $package;
    $this->imports= $imports;
    $this->declaration= $declaration;
  }

  /** @return string */
  public function package() { return $this->package; }

  /** @return string[] */
  public function imports() { return $this->imports; }

  /** @return lang.codedom.TypeDeclaration */
  public function declaration() { return $this->declaration; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return sprintf(
      "%s@(%s){\n%s  %s\n}",
      $this->getClassName(),
      $this->package ? 'package '.$this->package : 'main',
      $this->imports ? implode('', array_map(function($i) { return "  use $i\n"; }, $this->imports)) : '',
      str_replace("\n", "\n  ", $this->declaration->toString())
    );
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->package === $cmp->package &&
      Objects::equal($this->imports, $cmp->imports) &&
      Objects::equal($this->declaration, $cmp->declaration)
    );
  }
}