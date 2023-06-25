<?php namespace lang\unittest;

use lang\{ElementNotFoundException, IllegalArgumentException, Primitive, Type, XPClass};
use test\{Assert, Expect, Ignore, Test, Values};

class ImplementationTest {

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

  #[Test]
  public function typeDictionaryInstance() {
    $fixture= create('new lang.unittest.TypeDictionary<string>');
    Assert::equals(
      [Primitive::$STRING],
      typeof($fixture)->genericArguments()
    );
  }

  #[Test]
  public function typeDictionaryPutMethodKeyParameter() {
    $fixture= create('new lang.unittest.TypeDictionary<string>');
    Assert::equals(
      XPClass::forName('lang.Type'),
      typeof($fixture)->getMethod('put')->getParameter(0)->getType()
    );
  }

  #[Test, Ignore('Needs implementation change to copy all methods')]
  public function abstractTypeDictionaryPutMethodKeyParameter() {
    $fixture= Type::forName('lang.unittest.AbstractTypeDictionary<string>');
    Assert::equals(
      XPClass::forName('lang.Type'),
      $fixture->getMethod('put')->getParameter(0)->getType()
    );
  }

  #[Test]
  public function put() {
    $fixture= create('new lang.unittest.TypeDictionary<string>');
    $fixture->put(Primitive::$STRING, 'string');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function putInvalid() {
    $fixture= create('new lang.unittest.TypeDictionary<string>');
    $fixture->put($this, 'string');
  }

  #[Test]
  public function typeDictionaryInstanceInterface() {
    $fixture= create('new lang.unittest.TypeDictionary<string>');
    Assert::equals(
      [XPClass::forName('lang.Type'), Primitive::$STRING], 
      $this->interfaceNamed(typeof($fixture), 'lang.unittest.IDictionary')->genericArguments()
    );
  }

  #[Test]
  public function typeDictionaryClass() {
    $fixture= Type::forName('lang.unittest.TypeDictionary');
    Assert::equals(
      ['V'], 
      $fixture->genericComponents()
    );
  }

  #[Test]
  public function abstractTypeDictionaryClass() {
    $fixture= Type::forName('lang.unittest.AbstractTypeDictionary');
    Assert::equals(
      ['V'], 
      $fixture->genericComponents()
    );
  }

  #[Test]
  public function dictionaryInterfaceDefinition() {
    $fixture= Type::forName('lang.unittest.AbstractTypeDictionary');
    Assert::equals(
      ['K', 'V'], 
      $this->interfaceNamed($fixture, 'lang.unittest.IDictionary')->genericComponents()
    );
  }

  #[Test]
  public function dictionaryInterface() {
    $fixture= Type::forName('lang.unittest.AbstractTypeDictionary<string>');
    Assert::equals(
      [XPClass::forName('lang.Type'), Primitive::$STRING],
      $this->interfaceNamed($fixture, 'lang.unittest.IDictionary')->genericArguments()
    );
  }

  #[Test]
  public function closed() {
    Assert::null(
      Type::forName('lang.unittest.ListOf<string>')->getParentclass()
    );
  }

  #[Test]
  public function partiallyClosed() {
    Assert::equals(
      Type::forName('lang.unittest.Lookup<lang.Type, string>'),
      Type::forName('lang.unittest.TypeLookup<string>')->getParentclass()
    );
  }

  #[Test, Values([['', null], ['1', 1], ['Test', 'Test']])]
  public function type_variable_available($expect, $value) {
    $fixture= create('new lang.unittest.Unserializer<string>');
    Assert::equals($expect, $fixture->newInstance($value));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function type_aliasing() {
    $fixture= create('new lang.unittest.TypeDictionary<string>');
    $fixture->get(typeof($this));
  }
}