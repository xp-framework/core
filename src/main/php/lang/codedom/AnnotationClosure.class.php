<?php namespace lang\codedom;

use util\Objects;
use lang\IllegalStateException;

class AnnotationClosure extends \lang\Object {
  private $signature, $code;

  public function __construct($signature, $code) {
    $this->signature= $signature;
    $this->code= $code;
  }

  public function resolve($context, $imports) {
    $func= eval('return function('.$this->signature.') {'.$this->code.'};');
    if (!($func instanceof \Closure)) {
      if ($error= error_get_last()) {
        set_error_handler('__error', 0);
        trigger_error('clear_last_error');
        restore_error_handler();
      } else {
        $error= ['message' => 'Syntax error'];
      }
      throw new IllegalStateException('In `'.$this->code.'`: '.ucfirst($error['message']));
    }
    return $func;
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return $this->getClassName().'('.Objects::stringOf($this->backing).')';
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->signature === $cmp->signature &&
      $this->code === $cmp->code
    );
  }
}