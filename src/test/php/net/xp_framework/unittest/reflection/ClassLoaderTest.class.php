<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\ClassLoader;
use lang\ClassFormatException;
use lang\ClassNotFoundException;
use lang\ClassDependencyException;
use lang\ClassCastException;
use lang\IllegalStateException;
use lang\reflect\Package;
use lang\archive\Archive;
use lang\archive\ArchiveClassLoader;

/**
 * TestCase for classloading
 *
 * Makes use of the following classes in the package
 * net.xp_framework.unittest.reflection.classes:
 *
 * - ClassOne, ClassTwo - exist in the same directory as this class
 * - ClassThree, ClassFour - exist in "lib/three-and-four.xar"
 * - ClassFive - exists in "contained.xar" within "lib/three-and-four.xar"
 *
 * @see   xp://lang.ClassLoader
 * @see   xp://lang.XPClass#getClassLoader
 * @see   https://github.com/xp-framework/xp-framework/pull/235
 */
class ClassLoaderTest extends \unittest\TestCase {
  protected
    $libraryLoader   = null,
    $brokenLoader    = null,
    $containedLoader = null;

  /**
   * Register XAR
   *
   * @param  io.File $file
   * @return lang.IClassLoader
   */
  protected function registerXar($file) {
    return ClassLoader::registerLoader(new ArchiveClassLoader(new Archive($file)));
  }
    
  /**
   * Setup this test. Registeres class loaders deleates for the 
   * afforementioned XARs
   */
  public function setUp() {
    $lib= $this->getClass()->getPackage()->getPackage('lib');
    $this->libraryLoader= $this->registerXar($lib->getResourceAsStream('three-and-four.xar'));
    $this->brokenLoader= $this->registerXar($lib->getResourceAsStream('broken.xar'));
    $this->containedLoader= $this->registerXar($this->libraryLoader->getResourceAsStream('contained.xar'));
  }
  
  /**
   * Tear down this test. Removes classloader delegates registered 
   * during setUp()
   */
  public function tearDown() {
    ClassLoader::removeLoader($this->libraryLoader);
    ClassLoader::removeLoader($this->containedLoader);
    ClassLoader::removeLoader($this->brokenLoader);
  }

  #[@test, @values([
  #  'net.xp_framework.unittest.reflection.classes.ClassOne',
  #  'net.xp_framework.unittest.reflection.classes.InterfaceOne',
  #  'net.xp_framework.unittest.reflection.classes.TraitOne'
  #])]
  public function classloader_for_types_alongside_this_class($type) {
    $this->assertEquals(
      $this->getClass()->getClassLoader(),
      XPClass::forName($type)->getClassLoader()
    );
  }

  #[@test]
  public function twoClassesFromSamePlace() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassOne')->getClassLoader(),
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassTwo')->getClassLoader()
    );
  }

  #[@test]
  public function archiveClassLoader() {
    $this->assertInstanceOf(
      ArchiveClassLoader::class,
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassThree')->getClassLoader()
    );
  }

  #[@test]
  public function containedArchiveClassLoader() {
    $this->assertInstanceOf(
      ArchiveClassLoader::class,
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassFive')->getClassLoader()
    );
  }

  #[@test]
  public function twoClassesFromArchive() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassThree')->getClassLoader(),
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassFour')->getClassLoader()
    );
  }

  #[@test]
  public function loadClass() {
    $this->assertEquals(XPClass::forName('lang.Object'), ClassLoader::getDefault()->loadClass('lang.Object'));
  }

  #[@test]
  public function findThisClass() {
    $this->assertEquals(
      $this->getClass()->getClassLoader(),
      ClassLoader::getDefault()->findClass(nameof($this))
    );
  }

  #[@test]
  public function findNullClass() {
    $this->assertNull(ClassLoader::getDefault()->findClass(null));
  }

  #[@test]
  public function initializerCalled() {
    $name= 'net.xp_framework.unittest.reflection.LoaderTestClass';
    if (class_exists(literal($name), false)) {
      return $this->fail('Class "'.$name.'" may not exist!');
    }

    $this->assertTrue(ClassLoader::getDefault()
      ->loadClass($name)
      ->getMethod('initializerCalled')
      ->invoke(null)
    );
  }

  #[@test, @expect(ClassNotFoundException::class)]
  public function loadNonExistantClass() {
    ClassLoader::getDefault()->loadClass('@@NON-EXISTANT@@');
  }

  #[@test, @expect(class= ClassFormatException::class, withMessage= '/No types declared in .+/')]
  public function loadClassFileWithoutDeclaration() {
    XPClass::forName('net.xp_framework.unittest.reflection.classes.broken.NoClass');
  }

  #[@test, @expect(class= ClassFormatException::class, withMessage= '/File does not declare type `.+FalseClass`, but `.+TrueClass`/')]
  public function loadClassFileWithIncorrectDeclaration() {
    XPClass::forName('net.xp_framework.unittest.reflection.classes.broken.FalseClass');
  }

  #[@test, @expect(ClassDependencyException::class)]
  public function loadClassWithBrokenDependency() {
    XPClass::forName('net.xp_framework.unittest.reflection.classes.broken.BrokenDependencyClass');
  }

  #[@test]
  public function loadClassFileWithRecusionInStaticBlock() {
    with ($p= Package::forName('net.xp_framework.unittest.reflection.classes')); {
      $two= $p->loadClass('StaticRecursionTwo');
      $one= $p->loadClass('StaticRecursionOne');
      $this->assertEquals($two, $one->getField('two')->get(null));
    }
  }

  #[@test, @expect(IllegalStateException::class)]
  public function newInstance() {
    (new XPClass('DoesNotExist'))->reflect();
  }

  #[@test, @expect(ClassCastException::class)]
  public function newInstance__PHP_Incomplete_Class() {
    new XPClass(unserialize('O:12:"DoesNotExist":0:{}'));
  }
  
  #[@test]
  public function packageContents() {
    $this->assertEquals(
      ['net/', 'META-INF/', 'contained.xar'],
      $this->libraryLoader->packageContents('')
    );
  }

  #[@test]
  public function providesPackage() {
    $this->assertTrue($this->libraryLoader->providesPackage('net.xp_framework'));
  }
  
  #[@test]
  public function doesNotProvideAPackage() {
    $this->assertFalse($this->libraryLoader->providesPackage('net.xp_frame'));
  }

  #[@test]
  public function doesNotProvideClassone() {
    $this->assertFalse(ClassLoader::getDefault()
      ->providesClass('net.xp_framework.unittest.reflection.classes.Classone')
    );
  }

  #[@test, @expect(ClassNotFoundException::class)]
  public function loadingClassoneFails() {
    ClassLoader::getDefault()
      ->loadClass('net.xp_framework.unittest.reflection.classes.Classone')
    ;
  }

  #[@test]
  public function providesExistantUri() {
    $this->assertTrue(
      ClassLoader::getDefault()->providesUri('net/xp_framework/unittest/reflection/classes/ClassOne.class.php')
    );
  }

  #[@test]
  public function doesNotProvideNonExistantUri() {
    $this->assertFalse(
      ClassLoader::getDefault()->providesUri('non/existant/Class.class.php')
    );
  }

  #[@test]
  public function findExistantUri() {
    $cl= ClassLoader::getDefault();
    $this->assertEquals(
      $cl->findClass('net.xp_framework.unittest.reflection.classes.ClassOne'),
      $cl->findUri('net/xp_framework/unittest/reflection/classes/ClassOne.class.php')
    );
  }

  #[@test]
  public function cannotFindNontExistantUri() {
    $this->assertNull(ClassLoader::getDefault()->findUri('non/existant/Class.class.php'));
  }

  #[@test]
  public function loadUri() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.reflection.classes.ClassOne'),
      ClassLoader::getDefault()->loadUri('net/xp_framework/unittest/reflection/classes/ClassOne.class.php')
    );
  }

  #[@test, @expect(ClassNotFoundException::class)]
  public function loadNonExistantUri() {
    ClassLoader::getDefault()->loadUri('non/existant/Class.class.php');
  }
}
