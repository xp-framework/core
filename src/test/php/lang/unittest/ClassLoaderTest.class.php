<?php namespace lang\unittest;

use lang\archive\{Archive, ArchiveClassLoader};
use lang\reflect\Package;
use lang\{
  ClassCastException,
  ClassDependencyException,
  ClassFormatException,
  ClassLoader,
  ClassNotFoundException,
  IllegalStateException,
  Reflection,
  XPClass
};
use test\{After, Assert, Before, Expect, Test, Values};

class ClassLoaderTest {
  protected $libraryLoader, $brokenLoader, $containedLoader;

  /**
   * Register XAR
   *
   * @param  io.File $file
   * @return lang.IClassLoader
   */
  protected function registerXar($file) {
    return ClassLoader::registerLoader(new ArchiveClassLoader(new Archive($file)));
  }
    
  #[Before]
  public function setUp() {
    $lib= typeof($this)->getPackage();
    $this->libraryLoader= $this->registerXar($lib->getResourceAsStream('three-and-four.xar'));
    $this->brokenLoader= $this->registerXar($lib->getResourceAsStream('broken.xar'));
    $this->containedLoader= $this->registerXar($this->libraryLoader->getResourceAsStream('contained.xar'));
  }
  
  #[After]
  public function tearDown() {
    ClassLoader::removeLoader($this->libraryLoader);
    ClassLoader::removeLoader($this->containedLoader);
    ClassLoader::removeLoader($this->brokenLoader);
  }

  #[Test, Values(['lang.unittest.fixture.ClassOne', 'lang.unittest.fixture.InterfaceOne', 'lang.unittest.fixture.TraitOne'])]
  public function classloader_for_types_alongside_this_class($type) {
    Assert::equals(
      typeof($this)->getClassLoader(),
      XPClass::forName($type)->getClassLoader()
    );
  }

  #[Test]
  public function twoClassesFromSamePlace() {
    Assert::equals(
      XPClass::forName('lang.unittest.fixture.ClassOne')->getClassLoader(),
      XPClass::forName('lang.unittest.fixture.ClassTwo')->getClassLoader()
    );
  }

  #[Test]
  public function archiveClassLoader() {
    Assert::instance(
      ArchiveClassLoader::class,
      XPClass::forName('lang.unittest.fixture.ClassThree')->getClassLoader()
    );
  }

  #[Test]
  public function containedArchiveClassLoader() {
    Assert::instance(
      ArchiveClassLoader::class,
      XPClass::forName('lang.unittest.fixture.ClassFive')->getClassLoader()
    );
  }

  #[Test]
  public function twoClassesFromArchive() {
    Assert::equals(
      XPClass::forName('lang.unittest.fixture.ClassThree')->getClassLoader(),
      XPClass::forName('lang.unittest.fixture.ClassFour')->getClassLoader()
    );
  }

  #[Test]
  public function loadClass() {
    Assert::equals(XPClass::forName('lang.Value'), ClassLoader::getDefault()->loadClass('lang.Value'));
  }

  #[Test]
  public function findThisClass() {
    Assert::equals(
      typeof($this)->getClassLoader(),
      ClassLoader::getDefault()->findClass(nameof($this))
    );
  }

  #[Test]
  public function findNullClass() {
    Assert::null(ClassLoader::getDefault()->findClass(null));
  }

  #[Test]
  public function initializerCalled() {
    $name= 'lang.unittest.LoaderTestClass';
    if (class_exists(literal($name), false)) {
      throw new IllegalStateException('Class "'.$name.'" may not exist!');
    }

    Assert::true(Reflection::type(ClassLoader::getDefault()->loadClass($name))
      ->method('initializerCalled')
      ->invoke(null)
    );
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function loadNonExistantClass() {
    ClassLoader::getDefault()->loadClass('@@NON-EXISTANT@@');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/No types declared in .+/')]
  public function loadClassFileWithoutDeclaration() {
    XPClass::forName('lang.unittest.fixture.broken.NoClass');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/File does not declare type `.+FalseClass`, but `.+TrueClass`/')]
  public function loadClassFileWithIncorrectDeclaration() {
    XPClass::forName('lang.unittest.fixture.broken.FalseClass');
  }

  #[Test]
  public function loadClassFileWithRecursionInStaticBlock() {
    with ($p= Package::forName('lang.unittest.fixture')); {
      $two= $p->loadClass('StaticRecursionTwo');
      $one= $p->loadClass('StaticRecursionOne');
      Assert::equals($two, Reflection::type($one)->property('two')->get(null));
    }
  }

  #[Test, Expect(IllegalStateException::class)]
  public function newInstance() {
    (new XPClass('DoesNotExist'))->reflect();
  }

  #[Test, Expect(ClassCastException::class)]
  public function newInstance__PHP_Incomplete_Class() {
    new XPClass(unserialize('O:12:"DoesNotExist":0:{}'));
  }
  
  #[Test]
  public function packageContents() {
    Assert::equals(
      ['lang/', 'META-INF/', 'contained.xar'],
      $this->libraryLoader->packageContents('')
    );
  }

  #[Test]
  public function providesPackage() {
    Assert::true($this->libraryLoader->providesPackage('lang.unittest'));
  }
  
  #[Test]
  public function doesNotProvideAPackage() {
    Assert::false($this->libraryLoader->providesPackage('lang.unit'));
  }

  #[Test]
  public function doesNotProvideClassone() {
    Assert::false(ClassLoader::getDefault()
      ->providesClass('lang.unittest.fixture.Classone')
    );
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function loadingClassoneFails() {
    ClassLoader::getDefault()
      ->loadClass('lang.unittest.fixture.Classone')
    ;
  }

  #[Test]
  public function providesExistantUri() {
    Assert::true(
      ClassLoader::getDefault()->providesUri('lang/unittest/fixture/ClassOne.class.php')
    );
  }

  #[Test]
  public function doesNotProvideNonExistantUri() {
    Assert::false(
      ClassLoader::getDefault()->providesUri('non/existant/Class.class.php')
    );
  }

  #[Test]
  public function findExistantUri() {
    $cl= ClassLoader::getDefault();
    Assert::equals(
      $cl->findClass('lang.unittest.fixture.ClassOne'),
      $cl->findUri('lang/unittest/fixture/ClassOne.class.php')
    );
  }

  #[Test]
  public function cannotFindNontExistantUri() {
    Assert::null(ClassLoader::getDefault()->findUri('non/existant/Class.class.php'));
  }

  #[Test]
  public function loadUri() {
    Assert::equals(
      XPClass::forName('lang.unittest.fixture.ClassOne'),
      ClassLoader::getDefault()->loadUri('lang/unittest/fixture/ClassOne.class.php')
    );
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function loadNonExistantUri() {
    ClassLoader::getDefault()->loadUri('non/existant/Class.class.php');
  }
}