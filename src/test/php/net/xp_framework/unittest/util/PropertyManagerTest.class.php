<?php namespace net\xp_framework\unittest\util;

use unittest\TestCase;
use util\PropertyManager;
use util\ResourcePropertySource;
use lang\ClassLoader;
use unittest\actions\RuntimeVersion;
new import('lang.ResourceProvider');

/**
 * Tests for util.PropertyManager singleton
 *
 * @see   xp://util.PropertyManager
 */
class PropertyManagerTest extends TestCase {
  const RESOURCE_PATH = 'net/xp_framework/unittest/util/';

  /**
   * Creates a fixrture
   *
   * @return  util.PropertyManager
   */
  private function fixture() {
    $class= ClassLoader::getDefault()->defineClass('NonSingletonPropertyManager', 'util.PropertyManager', [], '{
      public static function newInstance() {
        return new self();
      }
    }');
    
    return $class->getMethod('newInstance')->invoke(null);
  }
  
  /**
   * Returns a PropertyManager configured with the directory this test
   * class lies in.
   *
   * @return  util.PropertyManager
   */
  private function preconfigured() {
    $f= $this->fixture();
    $f->appendSource(new ResourcePropertySource(self::RESOURCE_PATH));
    return $f;
  }

  #[@test]
  public function isSingleton() {
    $this->assertEquals(
      PropertyManager::getInstance()->hashCode(),
      PropertyManager::getInstance()->hashCode()
    );
  }
  
  #[@test]
  public function testCanAcquireNewInstance() {
    $instance= $this->fixture();
    $this->assertInstanceOf('util.PropertyManager', $instance);
    $this->assertNotEquals($instance->hashCode(), $this->fixture()->hashCode());
  }
  
  #[@test]
  public function registerProperties() {
    $fixture= $this->fixture();
    $this->assertFalse($fixture->hasProperties('props'));
    $fixture->register('props', \util\Properties::fromString('[section]'));
    
    $this->assertTrue($fixture->hasProperties('props'));
  }
  
  #[@test]
  public function hasConfiguredSourceProperties() {
    $this->assertTrue($this->preconfigured()->hasProperties('example'));
  }
  
  #[@test]
  public function doesNotHaveConfiguredSourceProperties() {
    $this->assertFalse($this->preconfigured()->hasProperties('does-not-exist'));
  }
  
  #[@test]
  public function getPropertiesReturnsSameObjectIfExactlyOneAvailable() {
    $fixture= $this->preconfigured();
    $this->assertEquals(
      $fixture->getProperties('example')->hashCode(),
      $fixture->getProperties('example')->hashCode()
    );
  }
  
  #[@test]
  public function registerOverwritesExistingProperties() {
    $fixture= $this->preconfigured();
    $fixture->register('example', \util\Properties::fromString('[any-section]'));
    $this->assertEquals('any-section', $fixture->getProperties('example')->getFirstSection());
  }

  #[@test]
  public function getProperties() {
    $prop= $this->preconfigured()->getProperties('example');
    $this->assertInstanceOf('util.PropertyAccess', $prop);
    $this->assertEquals('value', $prop->readString('section', 'key'));
  }

  #[@test]
  public function prependSource() {
    $path= new \util\FilesystemPropertySource('.');
    $this->assertEquals($path, $this->fixture()->prependSource($path));
  }

  #[@test]
  public function appendSource() {
    $path= new \util\FilesystemPropertySource('.');
    $this->assertEquals($path, $this->fixture()->appendSource($path));
  }

  #[@test]
  public function hasSource() {
    $path= new \util\FilesystemPropertySource(__DIR__.'/..');
    $fixture= $this->fixture();
    $this->assertFalse($fixture->hasSource($path));
  }

  #[@test]
  public function hasAppendedSource() {
    $path= new \util\FilesystemPropertySource(__DIR__.'/..');
    $fixture= $this->fixture();
    $fixture->appendSource($path);
    $this->assertTrue($fixture->hasSource($path));
  }

  #[@test]
  public function removeSource() {
    $path= new \util\FilesystemPropertySource(__DIR__.'/..');
    $fixture= $this->fixture();
    $this->assertFalse($fixture->removeSource($path));
  }

  #[@test]
  public function removeAppendedSource() {
    $path= new \util\FilesystemPropertySource(__DIR__.'/..');
    $fixture= $this->fixture();
    $fixture->appendSource($path);
    $this->assertTrue($fixture->removeSource($path));
    $this->assertFalse($fixture->hasSource($path));
  }

  #[@test]
  public function getPropertiesFromSecondSource() {
    $fixture= $this->preconfigured();
    $fixture->appendSource(new ResourcePropertySource('net/xp_framework/unittest/'));

    $this->assertEquals('value', $fixture->getProperties('example')->readString('section', 'key'));
  }

  #[@test]
  public function getSourcesInitiallyEmpty() {
    $this->assertEquals([],  $this->fixture()->getSources());
  }

  #[@test]
  public function getSourcesAfterAppendingOne() {
    $path= new \util\FilesystemPropertySource('.');
    $fixture= $this->fixture();
    $fixture->appendSource($path);
    $this->assertEquals(array($path), $fixture->getSources());
  }

  #[@test]
  public function getSourcesAfterPrependingOne() {
    $path= new \util\FilesystemPropertySource('.');
    $fixture= $this->fixture();
    $fixture->prependSource($path);
    $this->assertEquals(array($path), $fixture->getSources());
  }

  #[@test]
  public function getCompositeProperties() {
    $fixture= $this->preconfigured();

    // Register new Properties, with some value in existing section
    $fixture->register('example', \util\Properties::fromString('[section]
dynamic-value=whatever'));

    $prop= $fixture->getProperties('example');
    $this->assertInstanceOf('util.PropertyAccess', $prop);
    $this->assertFalse($prop instanceof \util\Properties);

    // Check key from example.ini is available
    $this->assertEquals('value', $fixture->getProperties('example')->readString('section', 'key'));

    // Check key from registered Properties is available
    $this->assertEquals('whatever', $prop->readString('section', 'dynamic-value'));
  }

  #[@test]
  public function memoryPropertiesAlwaysHavePrecendenceInCompositeProperties() {
    $fixture= $this->preconfigured();

    $this->assertEquals('value', $fixture->getProperties('example')->readString('section', 'key'));

    $fixture->register('example', \util\Properties::fromString('[section]
key="overwritten value"'));
    $this->assertEquals('overwritten value', $fixture->getProperties('example')->readString('section', 'key'));
  }

  #[@test]
  public function appendingSourcesOnlyAddsNewSources() {
    $fixture= $this->fixture();
    $fixture->appendSource(new ResourcePropertySource(self::RESOURCE_PATH));
    $fixture->appendSource(new ResourcePropertySource(self::RESOURCE_PATH));

    $this->assertInstanceOf('util.Properties', $fixture->getProperties('example'));
  }

  #[@test]
  public function getExistantProperties() {
    $p= $this->preconfigured()->getProperties('example');
    $this->assertInstanceOf('util.Properties', $p);
    $this->assertTrue($p->exists(), 'Should return an existant Properties instance');

  }

  #[@test, @expect('lang.ElementNotFoundException')]
  public function getNonExistantProperties() {
    $this->preconfigured()->getProperties('does-not-exist');
  }

  #[@test]
  public function setSource() {
    $fixture= $this->fixture();
    $fixture->setSources([]);

    $this->assertEquals([], $fixture->getSources());
  }

  #[@test]
  public function setSingleSource() {
    $source= new \util\FilesystemPropertySource('.');
    $fixture= $this->fixture();
    $fixture->setSources(array($source));

    $this->assertEquals(array($source), $fixture->getSources());
  }

  #[@test]
  public function setSources() {
    $one= new \util\FilesystemPropertySource('.');
    $two= new \util\FilesystemPropertySource('..');

    $fixture= $this->fixture();
    $fixture->setSources(array($one, $two));

    $this->assertEquals(array($one, $two), $fixture->getSources());
  }

  #[@test, @action(new RuntimeVersion('<7.0.0-dev'))]
  public function setIllegalSourceKeepsPreviousStateAndThrowsException() {
    $one= new \util\FilesystemPropertySource('.');

    $fixture= $this->fixture();
    try {
      $fixture->setSources(array($one, null));
      $this->fail('No exception thrown', null, 'lang.IllegalArgumentException');
    } catch (\lang\IllegalArgumentException $expected) {
    }

    $this->assertEquals([], $fixture->getSources());
  }

  #[@test, @action(new RuntimeVersion('>=7.0.0-dev'))]
  public function setIllegalSourceKeepsPreviousStateAndThrowsException7() {
    $one= new \util\FilesystemPropertySource('.');

    $fixture= $this->fixture();
    try {
      $fixture->setSources(array($one, null));
      $this->fail('No exception thrown', null, 'TypeException or TypeError');
    } catch (\TypeException $expected) {   // PHP 7 alpha1
    } catch (\TypeError  $expected) {      // PHP 7-dev w/ Throwable -> https://github.com/php/php-src/pull/1284
    }

    $this->assertEquals([], $fixture->getSources());
  }

  #[@test]
  public function passEmptySourcesResetsList() {
    $one= new \util\FilesystemPropertySource('.');

    $fixture= $this->fixture();
    $fixture->appendSource($one);

    $fixture->setSources([]);
    $this->assertEquals([], $fixture->getSources());
  }
}
