<?php namespace net\xp_framework\unittest\util;

use lang\{ClassLoader, ElementNotFoundException, IllegalArgumentException};
use unittest\actions\RuntimeVersion;
use unittest\{Expect, Test};
use util\{
  FilesystemPropertySource,
  Properties,
  PropertyAccess,
  PropertyManager,
  ResourcePropertySource
};
new import('lang.ResourceProvider');

/**
 * Tests for util.PropertyManager singleton
 *
 * @deprecated
 * @see   xp://util.PropertyManager
 */
class PropertyManagerTest extends \unittest\TestCase {
  const RESOURCE_PATH = 'net/xp_framework/unittest/util/';

  /**
   * Creates a fixrture
   *
   * @return  util.PropertyManager
   */
  private function fixture() {
    $class= ClassLoader::getDefault()->defineClass('NonSingletonPropertyManager', PropertyManager::class, [], '{
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

  #[Test]
  public function isSingleton() {
    $this->assertTrue(PropertyManager::getInstance() === PropertyManager::getInstance());
  }
  
  #[Test]
  public function testCanAcquireNewInstance() {
    $instance= $this->fixture();
    $this->assertInstanceOf(PropertyManager::class, $instance);
    $this->assertFalse($instance === $this->fixture());
  }
  
  #[Test]
  public function registerProperties() {
    $fixture= $this->fixture();
    $this->assertFalse($fixture->hasProperties('props'));
    $fixture->register('props', (new Properties())->load('[section]'));
    
    $this->assertTrue($fixture->hasProperties('props'));
  }
  
  #[Test]
  public function hasConfiguredSourceProperties() {
    $this->assertTrue($this->preconfigured()->hasProperties('example'));
  }
  
  #[Test]
  public function doesNotHaveConfiguredSourceProperties() {
    $this->assertFalse($this->preconfigured()->hasProperties('does-not-exist'));
  }
  
  #[Test]
  public function getPropertiesReturnsSameObjectIfExactlyOneAvailable() {
    $fixture= $this->preconfigured();
    $this->assertEquals(
      $fixture->getProperties('example')->hashCode(),
      $fixture->getProperties('example')->hashCode()
    );
  }
  
  #[Test]
  public function registerOverwritesExistingProperties() {
    $fixture= $this->preconfigured();
    $fixture->register('example', (new Properties())->load('[any-section]'));
    $this->assertEquals(['any-section', 'section'], iterator_to_array($fixture->getProperties('example')->sections()));
  }

  #[Test]
  public function getProperties() {
    $prop= $this->preconfigured()->getProperties('example');
    $this->assertInstanceOf(PropertyAccess::class, $prop);
    $this->assertEquals('value', $prop->readString('section', 'key'));
  }

  #[Test]
  public function prependSource() {
    $path= new FilesystemPropertySource('.');
    $this->assertEquals($path, $this->fixture()->prependSource($path));
  }

  #[Test]
  public function appendSource() {
    $path= new FilesystemPropertySource('.');
    $this->assertEquals($path, $this->fixture()->appendSource($path));
  }

  #[Test]
  public function hasSource() {
    $path= new FilesystemPropertySource(__DIR__.'/..');
    $fixture= $this->fixture();
    $this->assertFalse($fixture->hasSource($path));
  }

  #[Test]
  public function hasAppendedSource() {
    $path= new FilesystemPropertySource(__DIR__.'/..');
    $fixture= $this->fixture();
    $fixture->appendSource($path);
    $this->assertTrue($fixture->hasSource($path));
  }

  #[Test]
  public function removeSource() {
    $path= new FilesystemPropertySource(__DIR__.'/..');
    $fixture= $this->fixture();
    $this->assertFalse($fixture->removeSource($path));
  }

  #[Test]
  public function removeAppendedSource() {
    $path= new FilesystemPropertySource(__DIR__.'/..');
    $fixture= $this->fixture();
    $fixture->appendSource($path);
    $this->assertTrue($fixture->removeSource($path));
    $this->assertFalse($fixture->hasSource($path));
  }

  #[Test]
  public function getPropertiesFromSecondSource() {
    $fixture= $this->preconfigured();
    $fixture->appendSource(new ResourcePropertySource('net/xp_framework/unittest/'));

    $this->assertEquals('value', $fixture->getProperties('example')->readString('section', 'key'));
  }

  #[Test]
  public function getSourcesInitiallyEmpty() {
    $this->assertEquals([],  $this->fixture()->getSources());
  }

  #[Test]
  public function getSourcesAfterAppendingOne() {
    $path= new FilesystemPropertySource('.');
    $fixture= $this->fixture();
    $fixture->appendSource($path);
    $this->assertEquals([$path], $fixture->getSources());
  }

  #[Test]
  public function getSourcesAfterPrependingOne() {
    $path= new FilesystemPropertySource('.');
    $fixture= $this->fixture();
    $fixture->prependSource($path);
    $this->assertEquals([$path], $fixture->getSources());
  }

  #[Test]
  public function getCompositeProperties() {
    $fixture= $this->preconfigured();

    // Register new Properties, with some value in existing section
    $fixture->register('example', (new Properties())->load('[section]
dynamic-value=whatever'));

    $prop= $fixture->getProperties('example');
    $this->assertInstanceOf(PropertyAccess::class, $prop);
    $this->assertFalse($prop instanceof Properties);

    // Check key from example.ini is available
    $this->assertEquals('value', $fixture->getProperties('example')->readString('section', 'key'));

    // Check key from registered Properties is available
    $this->assertEquals('whatever', $prop->readString('section', 'dynamic-value'));
  }

  #[Test]
  public function memoryPropertiesAlwaysHavePrecendenceInCompositeProperties() {
    $fixture= $this->preconfigured();

    $this->assertEquals('value', $fixture->getProperties('example')->readString('section', 'key'));

    $fixture->register('example', (new Properties())->load('[section]
key="overwritten value"'));
    $this->assertEquals('overwritten value', $fixture->getProperties('example')->readString('section', 'key'));
  }

  #[Test]
  public function appendingSourcesOnlyAddsNewSources() {
    $fixture= $this->fixture();
    $fixture->appendSource(new ResourcePropertySource(self::RESOURCE_PATH));
    $fixture->appendSource(new ResourcePropertySource(self::RESOURCE_PATH));

    $this->assertInstanceOf(Properties::class, $fixture->getProperties('example'));
  }

  #[Test]
  public function getExistantProperties() {
    $p= $this->preconfigured()->getProperties('example');
    $this->assertInstanceOf(Properties::class, $p);
    $this->assertTrue($p->exists(), 'Should return an existant Properties instance');

  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function getNonExistantProperties() {
    $this->preconfigured()->getProperties('does-not-exist');
  }

  #[Test]
  public function setSource() {
    $fixture= $this->fixture();
    $fixture->setSources([]);

    $this->assertEquals([], $fixture->getSources());
  }

  #[Test]
  public function setSingleSource() {
    $source= new FilesystemPropertySource('.');
    $fixture= $this->fixture();
    $fixture->setSources([$source]);

    $this->assertEquals([$source], $fixture->getSources());
  }

  #[Test]
  public function setSources() {
    $one= new FilesystemPropertySource('.');
    $two= new FilesystemPropertySource('..');

    $fixture= $this->fixture();
    $fixture->setSources([$one, $two]);

    $this->assertEquals([$one, $two], $fixture->getSources());
  }

  #[Test]
  public function setIllegalSourceKeepsPreviousStateAndThrowsException() {
    $one= new FilesystemPropertySource('.');

    $fixture= $this->fixture();
    try {
      $fixture->setSources([$one, null]);
      $this->fail('No exception thrown', null, 'TypeError');
    } catch (\TypeError $expected) {
    }

    $this->assertEquals([], $fixture->getSources());
  }

  #[Test]
  public function passEmptySourcesResetsList() {
    $one= new FilesystemPropertySource('.');

    $fixture= $this->fixture();
    $fixture->appendSource($one);

    $fixture->setSources([]);
    $this->assertEquals([], $fixture->getSources());
  }
}