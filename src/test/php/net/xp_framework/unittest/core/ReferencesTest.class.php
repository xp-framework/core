<?php namespace net\xp_framework\unittest\core;

use lang\ClassLoader;
use unittest\Test;

/**
 * References test.
 */
class ReferencesTest extends \unittest\TestCase {

  static function __static() {
    
    // For singletonInstance test
    ClassLoader::defineClass('net.xp_framework.unittest.core.AnonymousSingleton', null, [], '{
      protected static $instance= NULL;

      static function getInstance() {
        if (!isset(self::$instance)) self::$instance= new AnonymousSingleton();
        return self::$instance;
      }
    }');

    // For returnNewObject and returnNewObjectViaReflection tests
    ClassLoader::defineClass('net.xp_framework.unittest.core.AnonymousList', null, [], '{
      public function __construct() {
        \net\xp_framework\unittest\core\ReferencesTest::registry("list", $this);
      }
    }');
    ClassLoader::defineClass('net.xp_framework.unittest.core.AnonymousFactory', null, [], '{
      static function factory() {
        return new AnonymousList();
      }
    }');
    ClassLoader::defineClass('net.xp_framework.unittest.core.AnonymousNewInstanceFactory', null, [], '{
      static function factory() {
        return \lang\XPClass::forName("net.xp_framework.unittest.core.AnonymousList")->newInstance();
      }
    }');
  }

  /**
   * Helper method that asserts to objects are references to each other
   *
   * @param   var $a
   * @param   var $b
   * @throws  unittest.AssertionFailedError
   */
  protected function assertReference($a, $b) {
    $this->assertTrue($a === $b);
  }

  #[Test]
  public function singletonInstance() {
    $s1= AnonymousSingleton::getInstance();
    $s2= AnonymousSingleton::getInstance();
    
    $this->assertReference($s1, $s2);
  }
  
  /**
   * Simulates static class member
   * 
   * @param   string key
   * @param   &var val
   * @return  &var
   */
  public static function registry($key, $val) {
    static $registry= [];
    
    if (NULL !== $val) $registry[$key]= $val;
    return $registry[$key];
  }
  
  #[Test]
  public function returnNewObject() {
    $object= AnonymousFactory::factory();
    $value= ReferencesTest::registry('list', $r= NULL);
    
    $this->assertReference($object, $value);
  }    

  #[Test]
  public function returnNewObjectViaMethodInvoke() {
    $class= \lang\XPClass::forName('net.xp_framework.unittest.core.AnonymousFactory');
    $factory= $class->getMethod('factory');
    $object= $factory->invoke($instance= NULL);
    $value= ReferencesTest::registry('list', $r= NULL);

    $this->assertReference($object, $value);
  }
  
  #[Test]
  public function returnNewObjectViaNewInstance() {
    $object= AnonymousNewInstanceFactory::factory();
    $value= ReferencesTest::registry('list', $r= NULL);
    
    $this->assertReference($object, $value);
  }
}