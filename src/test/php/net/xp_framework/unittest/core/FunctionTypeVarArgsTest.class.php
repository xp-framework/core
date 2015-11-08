<?php namespace net\xp_framework\unittest\core;

use unittest\actions\RuntimeVersion;
use lang\FunctionType;
use lang\Type;

#[@action(new RuntimeVersion('>=5.6.0'))]
class FunctionTypeVarArgsTest extends \unittest\TestCase {
  private static $compiled= [];

  /**
   * Returns a varargs function with a given signature. Uses caching.
   *
   * @param  string $signature
   * @return php.Closure
   */
  private function varargs($signature) {
    if (!isset(self::$compiled[$signature])) {
      self::$compiled[$signature]= eval('return function('.$signature.') { };');
    }
    return self::$compiled[$signature];
  }

  #[@test]
  public function isInstance() {
    $type= new FunctionType([], Type::$VAR);
    $this->assertTrue($type->isInstance($this->varargs('... $args')));
  }
}