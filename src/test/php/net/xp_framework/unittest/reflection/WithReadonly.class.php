<?php namespace net\xp_framework\unittest\reflection;

/** @property-read string $prop */
class WithReadonly {
  private $__readonly= ['prop' => null];

  public function __get($member) {
    if (!array_key_exists($member, $this->__readonly)) {
      trigger_error('Undefined property '.__CLASS__.'::'.$member, E_USER_WARNING);
    }

    return $this->__readonly[$member][0] ?? null;
  }

  public function __set($member, $value) {

    // Illegal reassignment
    if (isset($this->__readonly[$member])) {
      throw new \Error('Cannot modify readonly property '.__CLASS__.'::'.$member);
    }

    // Illegal initialization outside of private scope
    $caller= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
    $scope= $caller['class'] ?? null;
    if (__CLASS__ !== $scope && \lang\VirtualProperty::class !== $scope) {
      throw new \Error('Cannot initialize readonly property '.__CLASS__.'::'.$member.' from '.($scope
        ? 'scope '.$scope
        : 'global scope'
      ));
    }

    // Legal initialization
    $this->__readonly[$member]= [$value];
  }
}