<?php namespace net\xp_framework\unittest\core\generics;

use lang\Type;
use lang\Primitive;
use lang\XPClass;
use lang\ElementNotFoundException;
use lang\IllegalArgumentException;

/**
 * TestCase for instance reflection
 *
 * @see   xp://net.xp_framework.unittest.core.generics.TypeDictionary
 * @see   xp://net.xp_framework.unittest.core.generics.TypeLookup
 */
class ImplementationTest extends \unittest\TestCase {

  /**
   * Locate interface by a given name
   *
   * @param  lang.XPClass $class
   * @param  string $name
   * @return lang.XPClass
   * @throws lang.ElementNotFoundException
   */
  private function interfaceNamed($class, $name) {
    foreach ($class->getInterfaces() as $iface) {
      if (strstr($iface->getName(), $name)) return $iface;
    }
    throw new ElementNotFoundException('Class '.$class->getName().' does not implement '.$name);
  }

  #[@test]
  public function typeDictionaryInstance() {
    $fixture= create('new net.xp_framework.unittest.core.generics.TypeDictionary<string>');
    $this->assertEquals(
      [Primitive::$STRING],
      typeof($fixture)->genericArguments()
    );
  }

  #[@test]
  public function typeDictionaryPutMethodKeyParameter() {
    $fixture= create('new net.xp_framework.unittest.core.generics.TypeDictionary<string>');
    $this->assertEquals(
      XPClass::forName('lang.Type'),
      typeof($fixture)->getMethod('put')->getParameter(0)->getType()
    );
  }

  #[@test, @ignore('Needs implementation change to copy all methods')]
  public function abstractTypeDictionaryPutMethodKeyParameter() {
    $fixture= Type::forName('net.xp_framework.unittest.core.generics.AbstractTypeDictionary<string>');
    $this->assertEquals(
      XPClass::forName('lang.Type'),
      $fixture->getMethod('put')->getParameter(0)->getType()
    );
  }

  #[@test]
  public function put() {
    $fixture= create('new net.xp_framework.unittest.core.generics.TypeDictionary<string>');
    $fixture->put(Primitive::$STRING, 'string');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function putInvalid() {
    $fixture= create('new net.xp_framework.unittest.core.generics.TypeDictionary<string>');
    $fixture->put($this, 'string');
  }

  #[@test]
  public function typeDictionaryInstanceInterface() {
    $fixture= create('new net.xp_framework.unittest.core.generics.TypeDictionary<string>');
    $this->assertEquals(
      [XPClass::forName('lang.Type'), Primitive::$STRING], 
      $this->interfaceNamed(typeof($fixture), 'net.xp_framework.unittest.core.generics.IDictionary')->genericArguments()
    );
  }

  #[@test]
  public function typeDictionaryClass() {
    $fixture= Type::forName('net.xp_framework.unittest.core.generics.TypeDictionary');
    $this->assertEquals(
      ['V'], 
      $fixture->genericComponents()
    );
  }

  #[@test]
  public function abstractTypeDictionaryClass() {
    $fixture= Type::forName('net.xp_framework.unittest.core.generics.AbstractTypeDictionary');
    $this->assertEquals(
      ['V'], 
      $fixture->genericComponents()
    );
  }

  #[@test]
  public function dictionaryInterfaceDefinition() {
    $fixture= Type::forName('net.xp_framework.unittest.core.generics.AbstractTypeDictionary');
    $this->assertEquals(
      ['K', 'V'], 
      $this->interfaceNamed($fixture, 'net.xp_framework.unittest.core.generics.IDictionary')->genericComponents()
    );
  }

  #[@test]
  public function dictionaryInterface() {
    $fixture= Type::forName('net.xp_framework.unittest.core.generics.AbstractTypeDictionary<string>');
    $this->assertEquals(
      [XPClass::forName('lang.Type'), Primitive::$STRING],
      $this->interfaceNamed($fixture, 'net.xp_framework.unittest.core.generics.IDictionary')->genericArguments()
    );
  }

  #[@test]
  public function closed() {
    $this->assertNull(
      Type::forName('net.xp_framework.unittest.core.generics.ListOf<string>')->getParentclass()
    );
  }

  #[@test]
  public function partiallyClosed() {
    $this->assertEquals(
      Type::forName('net.xp_framework.unittest.core.generics.Lookup<lang.Type, string>'),
      Type::forName('net.xp_framework.unittest.core.generics.TypeLookup<string>')->getParentclass()
    );
  }

  #[@test, @values([['', null], ['1', 1], ['Test', 'Test']])]
  public function type_variable_available($expect, $value) {
    $fixture= create('new net.xp_framework.unittest.core.generics.Unserializer<string>');
    $this->assertEquals($expect, $fixture->newInstance($value));
  }
}
