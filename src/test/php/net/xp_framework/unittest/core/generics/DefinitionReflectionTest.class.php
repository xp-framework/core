<?php namespace net\xp_framework\unittest\core\generics;

use lang\XPClass;

class DefinitionReflectionTest extends AbstractDefinitionReflectionTest {
  
  /**
   * Creates fixture, a Lookup class
   *
   * @return lang.XPClass
   */  
  protected function fixtureClass() {
    return XPClass::forName('net.xp_framework.unittest.core.generics.Lookup');
  }

  /**
   * Creates fixture instance
   *
   * @return var
   */
  protected function fixtureInstance() {
    return create('new net.xp_framework.unittest.core.generics.Lookup<string, lang.Value>()');
  }
}