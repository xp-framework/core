<?php namespace net\xp_framework\unittest\reflection;

use lang\{ClassLoader, IllegalAccessException, XPClass};
use unittest\{BeforeClass, Expect, Test};

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
  #[BeforeClass]
  public static function initializeClasses() {
    self::$fixture= XPClass::forName('net.xp_framework.unittest.reflection.ProtectedAccessibilityFixture');
    self::$fixtureChild= XPClass::forName('net.xp_framework.unittest.reflection.ProtectedAccessibilityFixtureChild');
  }

  /**
   * Invoke protected constructor from here should not work
   *
   * @return void
   */
  #[Test, Expect(IllegalAccessException::class)]
  public function invokingProtectedConstructor() {
    self::$fixture->getConstructor()->newInstance([]);
  }

  #[Test]
  public function invokingProtectedConstructorFromSameClass() {
    $this->assertInstanceOf(self::$fixture, ProtectedAccessibilityFixture::construct(self::$fixture));
  }

  #[Test]
  public function invokingProtectedConstructorFromParentClass() {
    $this->assertInstanceOf(self::$fixture, ProtectedAccessibilityFixtureChild::construct(self::$fixture));
  }

  #[Test]
  public function invokingProtectedConstructorFromChildClass() {
    $this->assertInstanceOf(self::$fixtureChild, ProtectedAccessibilityFixtureChild::construct(self::$fixtureChild));
  }

  #[Test]
  public function invokingProtectedConstructorMadeAccessible() {
    $this->assertInstanceOf(self::$fixture, self::$fixture
      ->getConstructor()
      ->setAccessible(true)
      ->newInstance([])
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingProtectedMethod() {
    self::$fixture->getMethod('target')->invoke(ProtectedAccessibilityFixture::construct(self::$fixture));
  }

  #[Test]
  public function invokingProtectedMethodFromSameClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixture::invoke(self::$fixture));
  }

  #[Test]
  public function invokingProtectedMethodFromParentClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixtureChild::invoke(self::$fixture));
  }

  #[Test]
  public function invokingProtectedMethodFromChildClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixtureChild::invoke(self::$fixtureChild));
  }

  #[Test]
  public function invokingProtectedMethodMadeAccessible() {
    $this->assertEquals('Invoked', self::$fixture
      ->getMethod('target')
      ->setAccessible(true)
      ->invoke(ProtectedAccessibilityFixture::construct(self::$fixture))
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingProtectedStaticMethod() {
    self::$fixture->getMethod('staticTarget')->invoke(null);
  }

  #[Test]
  public function invokingProtectedStaticMethodFromSameClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixture::invokeStatic(self::$fixture));
  }

  #[Test]
  public function invokingProtectedStaticMethodFromParentClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixtureChild::invokeStatic(self::$fixture));
  }

  #[Test]
  public function invokingProtectedStaticMethodFromChildClass() {
    $this->assertEquals('Invoked', ProtectedAccessibilityFixtureChild::invokeStatic(self::$fixtureChild));
  }

  #[Test]
  public function invokingProtectedStaticMethodMadeAccessible() {
    $this->assertEquals('Invoked', self::$fixture
      ->getMethod('staticTarget')
      ->setAccessible(true)
      ->invoke(null)
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function readingProtectedMember() {
    self::$fixture->getField('target')->get(ProtectedAccessibilityFixture::construct(self::$fixture));
  }

  #[Test]
  public function readingProtectedMemberFromSameClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixture::read(self::$fixture));
  }

  #[Test]
  public function readingProtectedMemberFromParentClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixtureChild::read(self::$fixture));
  }

  #[Test]
  public function readingProtectedMemberFromChildClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixtureChild::read(self::$fixtureChild));
  }

  #[Test]
  public function readingProtectedMemberMadeAccessible() {
    $this->assertEquals('Target', self::$fixture
      ->getField('target')
      ->setAccessible(true)
      ->get(ProtectedAccessibilityFixture::construct(self::$fixture))
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function readingProtectedStaticMember() {
    self::$fixture->getField('staticTarget')->get(null);
  }

  #[Test]
  public function readingProtectedStaticMemberFromSameClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixture::readStatic(self::$fixture));
  }

  #[Test]
  public function readingProtectedStaticMemberFromParentClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixtureChild::readStatic(self::$fixture));
  }

  #[Test]
  public function readingProtectedStaticMemberFromChildClass() {
    $this->assertEquals('Target', ProtectedAccessibilityFixtureChild::readStatic(self::$fixtureChild));
  }

  #[Test]
  public function readingProtectedStaticMemberMadeAccessible() {
    $this->assertEquals('Target', self::$fixture
      ->getField('staticTarget')
      ->setAccessible(true)
      ->get(null)
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function writingProtectedMember() {
    self::$fixture->getField('target')->set(ProtectedAccessibilityFixture::construct(self::$fixture), null);
  }

  #[Test]
  public function writingProtectedMemberFromSameClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixture::write(self::$fixture));
  }

  #[Test]
  public function writingProtectedMemberFromParentClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixtureChild::write(self::$fixture));
  }

  #[Test]
  public function writingProtectedMemberFromChildClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixtureChild::write(self::$fixtureChild));
  }

  #[Test]
  public function writingProtectedMemberMadeAccessible() {
    with ($f= self::$fixture->getField('target'), $i= ProtectedAccessibilityFixture::construct(self::$fixture)); {
      $f->setAccessible(true);
      $f->set($i, 'Modified');
      $this->assertEquals('Modified', $f->get($i));
    }
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function writingProtectedStaticMember() {
    self::$fixture->getField('staticTarget')->set(null, 'Modified');
  }

  #[Test]
  public function writingProtectedStaticMemberFromSameClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixture::writeStatic(self::$fixture));
  }

  #[Test]
  public function writingProtectedStaticMemberFromParentClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixtureChild::writeStatic(self::$fixture));
  }

  #[Test]
  public function writingProtectedStaticMemberFromChildClass() {
    $this->assertEquals('Modified', ProtectedAccessibilityFixtureChild::writeStatic(self::$fixtureChild));
  }

  #[Test]
  public function writingProtectedStaticMemberMadeAccessible() {
    with ($f= self::$fixture->getField('staticTarget')); {
      $f->setAccessible(true);
      $f->set(null, 'Modified');
      $this->assertEquals('Modified', $f->get(null));
    }
  }
}