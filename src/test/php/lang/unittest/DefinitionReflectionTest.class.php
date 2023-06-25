<?php namespace lang\unittest;

use lang\XPClass;

class DefinitionReflectionTest extends AbstractDefinitionReflectionTest {
  
  /**
   * Creates fixture, a Lookup class
   *
   * @return lang.XPClass
   */  
  protected function fixtureClass() {
    return XPClass::forName('lang.unittest.Lookup');
  }

  /**
   * Creates fixture instance
   *
   * @return var
   */
  protected function fixtureInstance() {
    return create('new lang.unittest.Lookup<string, lang.Value>()');
  }
}