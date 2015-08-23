<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\ClassLoader;
use lang\IllegalAccessException;

/**
 * TestCase
 *
 * @see      xp://lang.reflect.Constructor
 * @see      xp://lang.reflect.Method
 * @see      xp://lang.reflect.Field
 */
class ProtectedAccessibilityTest extends \unittest\TestCase {
  protected static $fixture, $fixtureChild;
  
  /**
   * Initialize fixture and fixtureChild members
   *
   * @return void
   */
  #[@beforeClass]
  public static function initializeClasses() {
    self::$fixture= XPClass::forName('net.xp_framework.unittest.reflection.ProtectedAccessibilityFixture');
    self::$fixtureChild= XPClass::forName('net.xp_framework.unittest.reflection.ProtectedAccessibilityFixtureChild');
  }

  /**
   * Invoke protected constructor from here should not work
   *
   * @return void
   */
  #[@test, @expect(IllegalAccessException::class)]
  public function invokingProtectedConstructor() {
    self::$fixture->getConstructor()->newInstance([]);
  }

  #[@test]
  public function invokingProtectedConstructorFromSameClass() {
    $this->assertInstanceOf(self::$fixture, ProtectedAccessibilityFixture::construct(self::$fixture));
  }

  #[@test]
  public function invokingProtectedConstructorFromParentClass() {
    $this->assertInstanceOf(self::$fixture, ProtectedAccessibilityFixtureChild::construct(self::$fixture));
  }

  #[@test]
  public function invokingProtectedConstructorFromChildClass() {
    $this->assertInstanceOf(self::$fixtureChild, ProtectedAccessibilityFixtureChild::construct(self::$fixtureChild));
  }

  #[@test]
  public function invokingProtectedConstructorMadeAccessible() {
    $this->assertInstanceOf(self::$fixture, self::$fixture
      ->getConstructor()
      ->setAccessible(true)
      ->newInstance([])
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingProtectedMethod() {
    self::$fixture->getMethod('target')->invoke(ProtectedAccessibilityFixture::construct(self::$fixture));
  }

  #[@test]
  public function invokingProtectedMethodFromSameClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixture::invoke(self::$fixture));
  }

  #[@test]
  public function invokingProtectedMethodFromParentClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixtureChild::invoke(self::$fixture));
  }

  #[@test]
  public function invokingProtectedMethodFromChildClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixtureChild::invoke(self::$fixtureChild));
  }

  #[@test]
  public function invokingProtectedMethodMadeAccessible() {
    $this->assertEquals('Invoked', self::$fixture
      ->getMethod('target')
      ->setAccessible(true)
      ->invoke(ProtectedAccessibilityFixture::construct(self::$fixture))
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function invokingProtectedStaticMethod() {
    self::$fixture->getMethod('staticTarget')->invoke(null);
  }

  #[@test]
  public function invokingProtectedStaticMethodFromSameClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixture::invokeStatic(self::$fixture));
  }

  #[@test]
  public function invokingProtectedStaticMethodFromParentClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixtureChild::invokeStatic(self::$fixture));
  }

  #[@test]
  public function invokingProtectedStaticMethodFromChildClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixtureChild::invokeStatic(self::$fixtureChild));
  }

  #[@test]
  public function invokingProtectedStaticMethodMadeAccessible() {
    $this->assertEquals('Invoked', self::$fixture
      ->getMethod('staticTarget')
      ->setAccessible(true)
      ->invoke(null)
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function readingProtectedMember() {
    self::$fixture->getField('target')->get(ProtectedAccessibilityFixture::construct(self::$fixture));
  }

  #[@test]
  public function readingProtectedMemberFromSameClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixture::read(self::$fixture));
  }

  #[@test]
  public function readingProtectedMemberFromParentClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixtureChild::read(self::$fixture));
  }

  #[@test]
  public function readingProtectedMemberFromChildClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixtureChild::read(self::$fixtureChild));
  }

  #[@test]
  public function readingProtectedMemberMadeAccessible() {
    $this->assertEquals('Target', self::$fixture
      ->getField('target')
      ->setAccessible(true)
      ->get(ProtectedAccessibilityFixture::construct(self::$fixture))
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function readingProtectedStaticMember() {
    self::$fixture->getField('staticTarget')->get(null);
  }

  #[@test]
  public function readingProtectedStaticMemberFromSameClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixture::readStatic(self::$fixture));
  }

  #[@test]
  public function readingProtectedStaticMemberFromParentClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixtureChild::readStatic(self::$fixture));
  }

  #[@test]
  public function readingProtectedStaticMemberFromChildClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixtureChild::readStatic(self::$fixtureChild));
  }

  #[@test]
  public function readingProtectedStaticMemberMadeAccessible() {
    $this->assertEquals('Target', self::$fixture
      ->getField('staticTarget')
      ->setAccessible(true)
      ->get(null)
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function writingProtectedMember() {
    self::$fixture->getField('target')->set(ProtectedAccessibilityFixture::construct(self::$fixture), null);
  }

  #[@test]
  public function writingProtectedMemberFromSameClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixture::write(self::$fixture));
  }

  #[@test]
  public function writingProtectedMemberFromParentClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixtureChild::write(self::$fixture));
  }

  #[@test]
  public function writingProtectedMemberFromChildClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixtureChild::write(self::$fixtureChild));
  }

  #[@test]
  public function writingProtectedMemberMadeAccessible() {
    with ($f= self::$fixture->getField('target'), $i= ProtectedAccessibilityFixture::construct(self::$fixture)); {
      $f->setAccessible(true);
      $f->set($i, 'Modified');
      $this->assertEquals('Modified', $f->get($i));
    }
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function writingProtectedStaticMember() {
    self::$fixture->getField('staticTarget')->set(null, 'Modified');
  }

  #[@test]
  public function writingProtectedStaticMemberFromSameClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixture::writeStatic(self::$fixture));
  }

  #[@test]
  public function writingProtectedStaticMemberFromParentClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixtureChild::writeStatic(self::$fixture));
  }

  #[@test]
  public function writingProtectedStaticMemberFromChildClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixtureChild::writeStatic(self::$fixtureChild));
  }

  #[@test]
  public function writingProtectedStaticMemberMadeAccessible() {
    with ($f= self::$fixture->getField('staticTarget')); {
      $f->setAccessible(true);
      $f->set(null, 'Modified');
      $this->assertEquals('Modified', $f->get(null));
    }
  }
}
