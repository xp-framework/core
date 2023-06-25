<?php namespace lang\unittest;

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
      'ClassThree', 'ClassFour', 'Namespaced'
    ],
    $testPackages= ['fixture'];
  
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
      'lang.unittest.fixture', 
      Package::forName('lang.unittest.fixture')->getName()
    );
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function nonExistantPackage() {
    Package::forName('@@non-existant-package@@');
  }

  #[Test]
  public function providesTestClasses() {
    $p= Package::forName('lang.unittest.fixture');
    foreach (self::$testClasses as $name) {
      Assert::true($p->providesClass($name), $name);
    }
  }

  #[Test]
  public function loadClassByName() {
    Assert::equals(
      XPClass::forName('lang.unittest.fixture.ClassOne'),
      Package::forName('lang.unittest.fixture')->loadClass('ClassOne')
    );
  }

  #[Test]
  public function loadClassByQualifiedName() {
    Assert::equals(
      XPClass::forName('lang.unittest.fixture.ClassThree'),
      Package::forName('lang.unittest.fixture')->loadClass('lang.unittest.fixture.ClassThree')
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function loadClassFromDifferentPackage() {
    Package::forName('lang.unittest.fixture')->loadClass('lang.reflect.Method');
  }

  #[Test]
  public function classPackage() {
    Assert::equals(
      Package::forName('lang.unittest.fixture'),
      XPClass::forName('lang.unittest.fixture.ClassOne')->getPackage()
    );
  }

  #[Test]
  public function fileSystemClassPackageProvided() {
    $class= XPClass::forName('lang.unittest.fixture.ClassOne');
    Assert::true($class
      ->getClassLoader()
      ->providesPackage($class->getPackage()->getName())
    );
  }

  #[Test]
  public function archiveClassPackageProvided() {
    $class= XPClass::forName('lang.unittest.fixture.ClassThree');
    Assert::true($class
      ->getClassLoader()
      ->providesPackage($class->getPackage()->getName())
    );
  }

  #[Test]
  public function doesNotProvideNonExistantClass() {
    Assert::false(Package::forName('lang.unittest.fixture')->providesClass('@@non-existant-class@@'));
  }

  #[Test]
  public function getTestClassNames() {
    $base= 'lang.unittest.fixture';
    $names= Package::forName($base)->getClassNames();
    Assert::equals(sizeof(self::$testClasses), sizeof($names), Objects::stringOf($names));
    foreach ($names as $name) {
      Assert::true(
        in_array(substr($name, strlen($base) + 1), self::$testClasses), 
        $name
      );
    }
  }

  #[Test]
  public function getTestClasses() {
    $base= 'lang.unittest.fixture';
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
    $base= 'lang.unittest';
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
    $base= 'lang.unittest';
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
      Package::forName('lang.unittest.fixture'),
      Package::forName('lang.unittest')->getPackage('fixture')
    );
  }

  #[Test]
  public function loadPackageByQualifiedName() {
    Assert::equals(
      Package::forName('lang.unittest.fixture'),
      Package::forName('lang.unittest')->getPackage('lang.unittest.fixture')
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function loadPackageByDifferentName() {
    Package::forName('lang.unittest')->getPackage('lang.reflect');
  }

  #[Test]
  public function thisPackageHasNoComment() {
    Assert::null(
      Package::forName('lang.unittest')->getComment()
    );
  }

  #[Test]
  public function of_class() {
    Assert::equals(Package::forName('lang.unittest'), Package::of(self::class));
  }

  #[Test]
  public function of_nameof_this() {
    Assert::equals(Package::forName('lang.unittest'), Package::of(nameof($this)));
  }

  #[Test]
  public function of_typeof_this() {
    Assert::equals(Package::forName('lang.unittest'), Package::of(typeof($this)));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function of_class_from_non_existant_package() {
    Package::of('net.xp_framework.unittest.nonexistant.TestClass');
  }
}