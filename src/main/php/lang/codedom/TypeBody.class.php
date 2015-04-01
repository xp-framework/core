<?php namespace lang\codedom;

use util\Objects;

class TypeBody extends \lang\Object {
  private $members, $traits;

  public function __construct($members= [], $traits= []) {
    $this->members= $members;
    $this->traits= $traits;
  }

  /** @return lang.codedom.TraitUsage */
  public function traits() { return $this->traits; }

  /** @return lang.codedom.MemberDeclaration */
  public function members() { return $this->members; }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString($indent= '  ') {
    $s= '';
    $inset= "\n".$indent;
    foreach ($this->traits as $part) {
      $s.= $indent.str_replace("\n", $inset, $part->toString())."\n";
    }
    foreach ($this->members as $part) {
      $s.= $indent.str_replace("\n", $inset, $part->toString())."\n";
    }
    return $s;
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && (
      Objects::equal($this->members, $cmp->members) &&
      Objects::equal($this->traits, $cmp->traits)
    );
  }
}