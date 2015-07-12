<?php namespace unittest\mock\arguments;

use unittest\mock\MockProxyBuilder;
use lang\XPClass;
use lang\ClassLoader;

/**
 * Convenience class providing common argument matchers.
 *
 * @test  xp://net.xp_framework.unittest.tests.mock.ArgumentMatcherTest
 */
class Arg extends \lang\Object {
  private static $any;
  
  static function __static() {
    self::$any= new AnyMatcher();
  }

  /**
   * Accessor method for the any matcher.
   *
   */
  public static function any() {
    return self::$any;
  }
  
  /**
   * Accessor method for a dynamic matcher with a specified function.
   * 
   * @param   string func
   * @param   var classOrObject
   */
  public static function func($func, $classOrObj= null) {
    return new DynamicMatcher($func, $classOrObj);
  }
  
  /**
   * Accessor method for a type matcher.
   * 
   * @param   typeName string
   */
  public static function anyOfType($typeName) {
    $builder= new MockProxyBuilder();
    $builder->setOverwriteExisting(false);
    
    $interfaces= [XPClass::forName('unittest.mock.arguments.IArgumentMatcher')];
    $parentClass= null;
    
    $type= XPClass::forName($typeName);
    if ($type->isInterface()) {
      $interfaces[]= $type;
    } else {
      $parentClass= $type;
    }
    
    $proxyClass= $builder->createProxyClass(ClassLoader::getDefault(), $interfaces, $parentClass);
    return $proxyClass->newInstance(new TypeMatcher($typeName));
  }


  /**
   * Accessor method for a pattern matcher.
   * 
   * @param   pattern string
   */
  public static function match($pattern) {
    return new PatternMatcher($pattern);
  }
}
