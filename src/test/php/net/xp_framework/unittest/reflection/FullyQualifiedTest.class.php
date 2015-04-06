<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\archive\Archive;
use io\File;
use util\Date;
use lang\ResourceProvider;
use lang\ClassLoader;
use lang\archive\ArchiveClassLoader;
use lang\XPClass;

/**
 * Tests RFC #0037
 *
 * @see    rfc://0037
 */
class FullyQualifiedTest extends TestCase {

  static function __static() {
    ClassLoader::registerLoader(new ArchiveClassLoader(
      new Archive((new XPClass(__CLASS__))->getPackage()->getPackage('lib')->getResourceAsStream('fqcns.xar'))
    ));
    XPClass::forName('info.binford6100.Date');
    XPClass::forName('de.thekid.util.ObjectComparator');
  }

  #[@test]
  public function dateClassesCanCoexist() {
    $bd= new \info·binford6100·Date();
    $ud= new Date();

    $this->assertEquals('info.binford6100.Date', nameof($bd));
    $this->assertEquals('util.Date', nameof($ud));
  }

  #[@test]
  public function classObjectsAreNotEqual() {
    $bc= XPClass::forName('info.binford6100.Date');
    $uc= XPClass::forName('util.Date');
    $this->assertNotEquals($bc, $uc);
  }

  #[@test]
  public function dynamicallyLoaded() {
    $class= XPClass::forName('de.thekid.List');
    $this->assertEquals('de.thekid.List', $class->getName());
    $instance= $class->newInstance();
    $this->assertEquals('de.thekid.List@{}', $instance->toString());
  }

  #[@test]
  public function interfaceImplemented() {
    $this->assertEquals(
      [XPClass::forName('de.thekid.util.Comparator')],
      XPClass::forName('de.thekid.util.ObjectComparator')->getDeclaredInterfaces()
    );
  }
}
