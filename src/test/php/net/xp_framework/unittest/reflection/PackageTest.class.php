<?php namespace net\xp_framework\unittest\reflection;

use lang\archive\{Archive, ArchiveClassLoader};
use lang\reflect\Package;
use lang\{ClassLoader, ElementNotFoundException, IllegalArgumentException, XPClass};
use unittest\{Assert, Expect, Test};
use util\Objects;

class PackageTest {
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
  #[Before]
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
  #[After]
  public function tearDown() {
    ClassLoader::removeLoader($this->libraryLoader);
  }

  #[Test]
  public function packageName() {
    Assert::equals(
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
      Assert::true($p->providesClass($name), $name);
    }
  }

  #[Test]
  public function loadClassByName() {
    Assert::equals(
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassOne'),
      Package::forName('net.xp_framework.unittest.reflection.classes')->loadClass('ClassOne')
    );
  }

  #[Test]
  public function loadClassByQualifiedName() {
    Assert::equals(
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
    Assert::equals(
      Package::forName('net.xp_framework.unittest.reflection.classes'),
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassOne')->getPackage()
    );
  }

  #[Test]
  public function fileSystemClassPackageProvided() {
    $class= XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassOne');
    Assert::true($class
      ->getClassLoader()
      ->providesPackage($class->getPackage()->getName())
    );
  }

  #[Test]
  public function archiveClassPackageProvided() {
    $class= XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassThree');
    Assert::true($class
      ->getClassLoader()
      ->providesPackage($class->getPackage()->getName())
    );
  }

  #[Test]
  public function doesNotProvideNonExistantClass() {
    Assert::false(Package::forName('net.xp_framework.unittest.reflection.classes')->providesClass('@@non-existant-class@@'));
  }

  #[Test]
  public function getTestClassNames() {
    $base= 'net.xp_framework.unittest.reflection.classes';
    $names= Package::forName($base)->getClassNames();
    Assert::equals(sizeof(self::$testClasses), sizeof($names), Objects::stringOf($names));
    foreach ($names as $name) {
      Assert::true(
        in_array(substr($name, strlen($base)+ 1), self::$testClasses), 
        $name
      );
    }
  }

  #[Test]
  public function getTestClasses() {
    $base= 'net.xp_framework.unittest.reflection.classes';
    $classes= Package::forName($base)->getClasses();
    Assert::equals(sizeof(self::$testClasses), sizeof($classes), Objects::stringOf($classes));
    foreach ($classes as $class) {
      Assert::true(
        in_array(substr($class->getName(), strlen($base)+ 1), self::$testClasses), 
        $class->getName()
      );
    }
  }

  #[Test]
  public function getPackageNames() {
    $base= 'net.xp_framework.unittest.reflection';
    $names= Package::forName($base)->getPackageNames();
    Assert::equals(sizeof(self::$testPackages), sizeof($names), Objects::stringOf($names));
    foreach ($names as $name) {
      Assert::true(
        in_array(substr($name, strlen($base)+ 1), self::$testPackages), 
        $name
      );
    }
  }

  #[Test]
  public function getPackages() {
    $base= 'net.xp_framework.unittest.reflection';
    $packages= Package::forName($base)->getPackages();
    Assert::equals(sizeof(self::$testPackages), sizeof($packages), Objects::stringOf($packages));
    foreach ($packages as $package) {
      Assert::true(
        in_array(substr($package->getName(), strlen($base)+ 1), self::$testPackages), 
        $package->getName()
      );
    }
  }

  #[Test]
  public function loadPackageByName() {
    Assert::equals(
      Package::forName('net.xp_framework.unittest.reflection.classes'),
      Package::forName('net.xp_framework.unittest.reflection')->getPackage('classes')
    );
  }

  #[Test]
  public function loadPackageByQualifiedName() {
    Assert::equals(
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
    Assert::null(
      Package::forName('net.xp_framework.unittest.reflection')->getComment()
    );
  }

  #[Test]
  public function libPackageComment() {
    Assert::equals(
      'Fixture libraries for package reflection tests',
      trim(Package::forName('net.xp_framework.unittest.reflection.lib')->getComment())
    );
  }

  #[Test]
  public function of_class() {
    Assert::equals(Package::forName('net.xp_framework.unittest.reflection'), Package::of(self::class));
  }

  #[Test]
  public function of_nameof_this() {
    Assert::equals(Package::forName('net.xp_framework.unittest.reflection'), Package::of(nameof($this)));
  }

  #[Test]
  public function of_typeof_this() {
    Assert::equals(Package::forName('net.xp_framework.unittest.reflection'), Package::of(typeof($this)));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function of_class_from_non_existant_package() {
    Package::of('net.xp_framework.unittest.nonexistant.TestClass');
  }
}