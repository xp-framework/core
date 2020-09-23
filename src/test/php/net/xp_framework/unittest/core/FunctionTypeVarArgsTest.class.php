<?php namespace net\xp_framework\unittest\core;

use lang\{ClassLoader, FunctionType, Type};
use unittest\actions\RuntimeVersion;
use unittest\{Test, Values};

class FunctionTypeVarArgsTest extends \unittest\TestCase {
  private static $compiled= [];

  /**
   * Returns a varargs method with a given signature. Uses caching.
   *
   * @param  string $signature
   * @return string[] A reference to the method
   */
  private function compile($signature, $apidoc= []) {
    $class= nameof($this).md5($signature);
    if (!isset(self::$compiled[$class])) {
      self::$compiled[$class]= ClassLoader::defineClass($class, null, [], '{
        /**
         '.implode("\n* ", $apidoc).'
         */
        public static function fixture('.$signature.') { }
      }');
    }
    return [$class, 'fixture'];
  }

  private function singleParam() {
    yield [new FunctionType(null, Type::$VAR)];
    yield [new FunctionType([], Type::$VAR)];
    yield [new FunctionType([Type::$VAR], Type::$VAR)];
    yield [new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR)];
  }

  private function arrayParameter() {
    yield [new FunctionType(null, Type::$VAR)];
    yield [new FunctionType([Type::$ARRAY], Type::$VAR)];
    yield [new FunctionType([Type::$ARRAY, Type::$VAR], Type::$VAR)];
    yield [new FunctionType([Type::$ARRAY, Type::$VAR, Type::$VAR], Type::$VAR)];
  }

  #[Test, Values('singleParam')]
  public function singleParam_vararg_parameter_via_syntax($type) {
    $this->assertTrue($type->isInstance($this->compile('... $args')));
  }

  #[Test, Values('singleParam')]
  public function single_vararg_parameter_via_apidoc($type) {
    $this->assertTrue($type->isInstance($this->compile('', ['@param  var... $args]'])));
  }

  #[Test, Values('arrayParameter')]
  public function array_parameter_followed_by_vararg_parameter_via_syntax($type) {
    $this->assertTrue($type->isInstance($this->compile('array $tokens, ... $args')));
  }

  #[Test, Values('arrayParameter')]
  public function array_parameter_followed_by_vararg_parameter_via_apidoc($type) {
    $this->assertTrue($type->isInstance($this->compile('array $tokens', [
      '@param  var[] $tokens',
      '@param var... $args'
    ])));
  }
}