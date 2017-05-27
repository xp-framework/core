<?php namespace net\xp_framework\unittest\core;

use unittest\actions\RuntimeVersion;
use lang\FunctionType;
use lang\Type;
use lang\ClassLoader;

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

  #[@test, @values([
  #  [new FunctionType(null, Type::$VAR)],
  #  [new FunctionType([], Type::$VAR)],
  #  [new FunctionType([Type::$VAR], Type::$VAR)],
  #  [new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR)]
  #])]
  public function single_vararg_parameter_via_syntax($type) {
    $this->assertTrue($type->isInstance($this->compile('... $args')));
  }

  #[@test, @values([
  #  [new FunctionType(null, Type::$VAR)],
  #  [new FunctionType([], Type::$VAR)],
  #  [new FunctionType([Type::$VAR], Type::$VAR)],
  #  [new FunctionType([Type::$VAR, Type::$VAR], Type::$VAR)]
  #])]
  public function single_vararg_parameter_via_apidoc($type) {
    $this->assertTrue($type->isInstance($this->compile('', ['@param  var... $args]'])));
  }

  #[@test, @values([
  #  [new FunctionType(null, Type::$VAR)],
  #  [new FunctionType([Type::$ARRAY], Type::$VAR)],
  #  [new FunctionType([Type::$ARRAY, Type::$VAR], Type::$VAR)],
  #  [new FunctionType([Type::$ARRAY, Type::$VAR, Type::$VAR], Type::$VAR)]
  #])]
  public function array_parameter_followed_by_vararg_parameter_via_syntax($type) {
    $this->assertTrue($type->isInstance($this->compile('array $tokens, ... $args')));
  }

  #[@test, @values([
  #  [new FunctionType(null, Type::$VAR)],
  #  [new FunctionType([Type::$ARRAY], Type::$VAR)],
  #  [new FunctionType([Type::$ARRAY, Type::$VAR], Type::$VAR)],
  #  [new FunctionType([Type::$ARRAY, Type::$VAR, Type::$VAR], Type::$VAR)]
  #])]
  public function array_parameter_followed_by_vararg_parameter_via_apidoc($type) {
    $this->assertTrue($type->isInstance($this->compile('array $tokens', ['@param  var[] $tokens', '@param var... $args'])));
  }
}