<?php namespace lang\codedom;

use lang\Objects;

class AnnotationInstance extends \lang\Object { use ResolvingType;
  private $type;

  public function __construct($type, $arguments) {
    $this->type= $type;
    $this->arguments= $arguments;
  }

  public function resolve($context, $imports) {
    $type= $this->typeOf($this->type, $context, $imports);
    if ($type->hasConstructor()) {
      return $type->getConstructor()->newInstance(array_map(
        function($arg) use($context, $imports) { return $arg->resolve($context, $imports); },
        $this->arguments
      ));
    } else {
      return $type->newInstance();
    }
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return $this->getClassName().'(new '.$this->type.'('.Objects::stringOf($this->arguments).'))';
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && (
      Objects::equal($this->arguments, $cmp->arguments) &&
      Objects::equal($this->type, $cmp->type)
    );
  }
}