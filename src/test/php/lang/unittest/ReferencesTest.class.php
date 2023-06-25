<?php namespace lang\unittest;

use lang\{ClassLoader, Reflection};
use test\{Assert, Before, Test};

class ReferencesTest {

  /**
   * Helper method that asserts to objects are references to each other
   *
   * @param  var $a
   * @param  var $b
   * @throws unittest.AssertionFailedError
   */
  private function assertReference($a, $b) {
    Assert::true($a === $b);
  }

  #[Before]
  public function fixtures() {
    
    // For singletonInstance test
    ClassLoader::defineClass('lang.unittest.AnonymousSingleton', null, [], '{
      protected static $instance= NULL;

      static function getInstance() {
        if (!isset(self::$instance)) self::$instance= new AnonymousSingleton();
        return self::$instance;
      }
    }');

    // For returnNewObject and returnNewObjectViaReflection tests
    ClassLoader::defineClass('lang.unittest.AnonymousList', null, [], '{
      public function __construct() {
        \lang\unittest\ReferencesTest::registry("list", $this);
      }
    }');
    ClassLoader::defineClass('lang.unittest.AnonymousFactory', null, [], '{
      static function factory() {
        return new AnonymousList();
      }
    }');
    ClassLoader::defineClass('lang.unittest.AnonymousNewInstanceFactory', null, [], '{
      static function factory() {
        return \lang\XPClass::forName("lang.unittest.AnonymousList")->newInstance();
      }
    }');
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
    $object= Reflection::type(AnonymousNewInstanceFactory::class)->method('factory')->invoke(null);
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