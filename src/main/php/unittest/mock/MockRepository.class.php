<?php namespace unittest\mock;

use lang\Type;
use lang\XPClass;
use lang\IllegalArgumentException;
use lang\ClassLoader;

/**
 * Class for creating mock/stub instances of arbitrary types
 *
 * @test  xp://net.xp_framework.unittest.tests.mock.MockeryTest
 */
class MockRepository extends \lang\Object {
  private $mocks= [];

  /**
   * Builds a stub instance for the specified type.
   *
   * @param   string typeName
   * @param   boolean overrideAll
   * @return  lang.Object
   */
  public function createMock($typeName, $overrideAll= true) {
    $type= Type::forName($typeName);
    if (!($type instanceof XPClass)) {
      throw new IllegalArgumentException('Cannot mock other types than XPClass types.');
    }

    $parentClass= null;
    $interfaces= [XPClass::forName('unittest.mock.IMock')];
    if ($type->isInterface()) {
      $interfaces[]= $type;
    } else {
      $parentClass= $type;
    }
    
    $proxy= new MockProxyBuilder();
    $proxy->setOverwriteExisting($overrideAll);
    $proxyClass= $proxy->createProxyClass(ClassLoader::getDefault(), $interfaces, $parentClass);
    $mock= $proxyClass->newInstance(new MockProxy());
    $this->mocks[]= $mock;
    return $mock;
  }
  /**
   * Replays all mocks.
   *
   * @return void
   */
  public function replayAll() {
    foreach($this->mocks as $mock) {
      $mock->_replayMock();
    }
  }

  /**
   * Verifies all mocks.
   *
   * @return void
   */
  public function verifyAll() {
    foreach($this->mocks as $mock) {
      $mock->_verifyMock();
    }
  }
}
