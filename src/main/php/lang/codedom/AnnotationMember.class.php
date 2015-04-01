<?php namespace lang\codedom;

use lang\Objects;
use lang\IllegalAccessException;
use lang\reflect\Modifiers;

class AnnotationMember extends \lang\Object { use ResolvingType;
  private $type;

  public function __construct($type, $name) {
    $this->type= $type;
    $this->name= $name;
  }

  public function resolve($context, $imports) {
    $type= $this->typeOf($this->type, $context, $imports);
    if ('$' === $this->name{0}) {
      $field= $type->getField(substr($this->name, 1));
      $m= $field->getModifiers();
      if ($m & MODIFIER_PUBLIC) {
        return $field->get(null);
      } else if (($m & MODIFIER_PROTECTED) && $type->isAssignableFrom($context)) {
        return $field->setAccessible(true)->get(null);
      } else if (($m & MODIFIER_PRIVATE) && $type->getName() === $context) {
        return $field->setAccessible(true)->get(null);
      } else {
        throw new IllegalAccessException(sprintf(
          'Cannot access %s field %s::$%s',
          implode(' ', Modifiers::namesOf($m)),
          $type->getName(),
          $field->getName()
        ));
      }
      return $type->getField(substr($this->name, 1))->setAccessible(true)->get(null);
    } else if ('class' === $this->name) {
      return ltrim($type->literal(), '\\');
    } else {
      return $type->getConstant($this->name);
    }
  }

  /**
   * Returns whether a given value is equal to this code unit
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && (
      $this->name === $cmp->name &&
      $this->type === $cmp->type
    );
  }
}