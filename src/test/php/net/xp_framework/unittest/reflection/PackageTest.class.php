<?php namespace net\xp_framework\unittest\reflection;

use lang\archive\{Archive, ArchiveClassLoader};
use lang\reflect\Package;
use lang\{ClassLoader, ElementNotFoundException, IllegalArgumentException, XPClass};
use unittest\{Expect, Test};
use util\Objects;

/**
 * TestCase
 *
 * @see   xp://lang.reflect.Package
 */
class PackageTest extends \unittest\TestCase {
  protected static
    $testClasses= [
      'ClassOne', 'ClassTwo', 'RecursionOne', 'RecursionTwo', 'InterfaceOne',
      'TraitOne', 'UsingOne', 'StaticRecursionOne', 'StaticRecursionTwo',
      'ClassThree', 'ClassFour'
    ],
    $testPackages= ['classes', 'lib'];
  
  protected $libraryLoader;

  /**
   * Setup this test. Registeres class loaders deleates for the 
   * afforementioned XARs
   *
   * @return void
   */
  public function setUp() {
    $this->libraryLoader= ClassLoader::registerLoader(new ArchiveClassLoader(new Archive((new XPClass(self::class))
      ->getPackage()
      ->getPackage('lib')
      ->getResourceAsStream('three-and-four.xar')
    )));
  }
  
  /**
   * Tear down this test. Removes classloader delegates registered 
   * during setUp()
   *
   * @return void
   */
  public function tearDown() {
    ClassLoader::removeLoader($this->libraryLoader);
  }

  #[Test]
  public function packageName() {
    $this->assertEquals(
      'net.xp_framework.unittest.reflection.classes', 
      Package::forName('net.xp_framework.unittest.reflection.classes')->getName()
    );
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function nonExistantPackage() {
    Package::forName('@@non-existant-package@@');
  }

  #[Test]
  public function providesTestClasses() {
    $p= Package::forName('net.xp_framework.unittest.reflection.classes');
    foreach (self::$testClasses as $name) {
      $this->assertTrue($p->providesClass($name), $name);
    }
  }

  #[Test]
  public function loadClassByName() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassOne'),
      Package::forName('net.xp_framework.unittest.reflection.classes')->loadClass('ClassOne')
    );
  }

  #[Test]
  public function loadClassByQualifiedName() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassThree'),
      Package::forName('net.xp_framework.unittest.reflection.classes')->loadClass('net.xp_framework.unittest.reflection.classes.ClassThree')
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function loadClassFromDifferentPackage() {
    Package::forName('net.xp_framework.unittest.reflection.classes')->loadClass('lang.reflect.Method');
  }

  #[Test]
  public function classPackage() {
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.reflection.classes'),
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassOne')->getPackage()
    );
  }

  #[Test]
  public function fileSystemClassPackageProvided() {
    $class= XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassOne');
    $this->assertTrue($class
      ->getClassLoader()
      ->providesPackage($class->getPackage()->getName())
    );
  }

  #[Test]
  public function archiveClassPackageProvided() {
    $class= XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassThree');
    $this->assertTrue($class
      ->getClassLoader()
      ->providesPackage($class->getPackage()->getName())
    );
  }

  #[Test]
  public function doesNotProvideNonExistantClass() {
    $this->assertFalse(Package::forName('net.xp_framework.unittest.reflection.classes')->providesClass('@@non-existant-class@@'));
  }

  #[Test]
  public function getTestClassNames() {
    $base= 'net.xp_framework.unittest.reflection.classes';
    $names= Package::forName($base)->getClassNames();
    $this->assertEquals(sizeof(self::$testClasses), sizeof($names), Objects::stringOf($names));
    foreach ($names as $name) {
      $this->assertTrue(
        in_array(substr($name, strlen($base)+ 1), self::$testClasses), 
        $name
      );
    }
  }

  #[Test]
  public function getTestClasses() {
    $base= 'net.xp_framework.unittest.reflection.classes';
    $classes= Package::forName($base)->getClasses();
    $this->assertEquals(sizeof(self::$testClasses), sizeof($classes), Objects::stringOf($classes));
    foreach ($classes as $class) {
      $this->assertTrue(
        in_array(substr($class->getName(), strlen($base)+ 1), self::$testClasses), 
        $class->getName()
      );
    }
  }

  #[Test]
  public function getPackageNames() {
    $base= 'net.xp_framework.unittest.reflection';
    $names= Package::forName($base)->getPackageNames();
    $this->assertEquals(sizeof(self::$testPackages), sizeof($names), Objects::stringOf($names));
    foreach ($names as $name) {
      $this->assertTrue(
        in_array(substr($name, strlen($base)+ 1), self::$testPackages), 
        $name
      );
    }
  }

  #[Test]
  public function getPackages() {
    $base= 'net.xp_framework.unittest.reflection';
    $packages= Package::forName($base)->getPackages();
    $this->assertEquals(sizeof(self::$testPackages), sizeof($packages), Objects::stringOf($packages));
    foreach ($packages as $package) {
      $this->assertTrue(
        in_array(substr($package->getName(), strlen($base)+ 1), self::$testPackages), 
        $package->getName()
      );
    }
  }

  #[Test]
  public function loadPackageByName() {
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.reflection.classes'),
      Package::forName('net.xp_framework.unittest.reflection')->getPackage('classes')
    );
  }

  #[Test]
  public function loadPackageByQualifiedName() {
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.reflection.classes'),
      Package::forName('net.xp_framework.unittest.reflection')->getPackage('net.xp_framework.unittest.reflection.classes')
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function loadPackageByDifferentName() {
    Package::forName('net.xp_framework.unittest.reflection')->getPackage('lang.reflect');
  }

  #[Test]
  public function thisPackageHasNoComment() {
    $this->assertNull(
      Package::forName('net.xp_framework.unittest.reflection')->getComment()
    );
  }

  #[Test]
  public function libPackageComment() {
    $this->assertEquals(
      'Fixture libraries for package reflection tests',
      trim(Package::forName('net.xp_framework.unittest.reflection.lib')->getComment())
    );
  }

  #[Test]
  public function of_class() {
    $this->assertEquals(Package::forName('net.xp_framework.unittest.reflection'), Package::of(self::class));
  }

  #[Test]
  public function of_nameof_this() {
    $this->assertEquals(Package::forName('net.xp_framework.unittest.reflection'), Package::of(nameof($this)));
  }

  #[Test]
  public function of_typeof_this() {
    $this->assertEquals(Package::forName('net.xp_framework.unittest.reflection'), Package::of(typeof($this)));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function of_class_from_non_existant_package() {
    Package::of('net.xp_framework.unittest.nonexistant.TestClass');
  }
}