<?php namespace lang\codedom;

use util\Objects;

class CodeUnit extends \lang\Object {
  private $package, $imports, $declaration;

  public function __construct($package, $imports, $declaration) {
    $this->package= $package;
    $this->imports= $imports;
    $this->declaration= $declaration;
  }

  public function toString() {
    return sprintf(
      "%s@(%s){\n%s  %s\n}",
      $this->getClassName(),
      $this->package ? 'package '.$this->package : 'main',
      $this->imports ? implode('', array_map(function($i) { return "  use $i\n"; }, $this->imports)) : '',
      str_replace("\n", "\n  ", $this->declaration->toString())
    );
  }

  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->package === $cmp->package &&
      Objects::equal($this->imports, $cmp->imports) &&
      Objects::equal($this->declaration, $cmp->declaration)
    );
  }
}