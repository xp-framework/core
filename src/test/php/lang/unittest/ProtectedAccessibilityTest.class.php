<?php namespace lang\unittest;

use lang\{ClassLoader, IllegalAccessException, XPClass};
use unittest\{Assert, Before, Expect, Test};

class ProtectedAccessibilityTest {
  protected static $fixture, $fixtureChild;
  
  #[Before]
  public static function initializeClasses() {
    self::$fixture= XPClass::forName('lang.unittest.ProtectedAccessibilityFixture');
    self::$fixtureChild= XPClass::forName('lang.unittest.ProtectedAccessibilityFixtureChild');
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingProtectedConstructor() {
    self::$fixture->getConstructor()->newInstance([]);
  }

  #[Test]
  public function invokingProtectedConstructorFromSameClass() {
    Assert::instance(self::$fixture, ProtectedAccessibilityFixture::construct(self::$fixture));
  }

  #[Test]
  public function invokingProtectedConstructorFromParentClass() {
    Assert::instance(self::$fixture, ProtectedAccessibilityFixtureChild::construct(self::$fixture));
  }

  #[Test]
  public function invokingProtectedConstructorFromChildClass() {
    Assert::instance(self::$fixtureChild, ProtectedAccessibilityFixtureChild::construct(self::$fixtureChild));
  }

  #[Test]
  public function invokingProtectedConstructorMadeAccessible() {
    Assert::instance(self::$fixture, self::$fixture
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
    Assert::equals('Invoked', ProtectedAccessibilityFixture::invoke(self::$fixture));
  }

  #[Test]
  public function invokingProtectedMethodFromParentClass() {
    Assert::equals('Invoked', ProtectedAccessibilityFixtureChild::invoke(self::$fixture));
  }

  #[Test]
  public function invokingProtectedMethodFromChildClass() {
    Assert::equals('Invoked', ProtectedAccessibilityFixtureChild::invoke(self::$fixtureChild));
  }

  #[Test]
  public function invokingProtectedMethodMadeAccessible() {
    Assert::equals('Invoked', self::$fixture
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
    Assert::equals('Invoked', ProtectedAccessibilityFixture::invokeStatic(self::$fixture));
  }

  #[Test]
  public function invokingProtectedStaticMethodFromParentClass() {
    Assert::equals('Invoked', ProtectedAccessibilityFixtureChild::invokeStatic(self::$fixture));
  }

  #[Test]
  public function invokingProtectedStaticMethodFromChildClass() {
    Assert::equals('Invoked', ProtectedAccessibilityFixtureChild::invokeStatic(self::$fixtureChild));
  }

  #[Test]
  public function invokingProtectedStaticMethodMadeAccessible() {
    Assert::equals('Invoked', self::$fixture
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
    Assert::equals('Target', ProtectedAccessibilityFixture::read(self::$fixture));
  }

  #[Test]
  public function readingProtectedMemberFromParentClass() {
    Assert::equals('Target', ProtectedAccessibilityFixtureChild::read(self::$fixture));
  }

  #[Test]
  public function readingProtectedMemberFromChildClass() {
    Assert::equals('Target', ProtectedAccessibilityFixtureChild::read(self::$fixtureChild));
  }

  #[Test]
  public function readingProtectedMemberMadeAccessible() {
    Assert::equals('Target', self::$fixture
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
    Assert::equals('Target', ProtectedAccessibilityFixture::readStatic(self::$fixture));
  }

  #[Test]
  public function readingProtectedStaticMemberFromParentClass() {
    Assert::equals('Target', ProtectedAccessibilityFixtureChild::readStatic(self::$fixture));
  }

  #[Test]
  public function readingProtectedStaticMemberFromChildClass() {
    Assert::equals('Target', ProtectedAccessibilityFixtureChild::readStatic(self::$fixtureChild));
  }

  #[Test]
  public function readingProtectedStaticMemberMadeAccessible() {
    Assert::equals('Target', self::$fixture
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
    Assert::equals('Modified', ProtectedAccessibilityFixture::write(self::$fixture));
  }

  #[Test]
  public function writingProtectedMemberFromParentClass() {
    Assert::equals('Modified', ProtectedAccessibilityFixtureChild::write(self::$fixture));
  }

  #[Test]
  public function writingProtectedMemberFromChildClass() {
    Assert::equals('Modified', ProtectedAccessibilityFixtureChild::write(self::$fixtureChild));
  }

  #[Test]
  public function writingProtectedMemberMadeAccessible() {
    with ($f= self::$fixture->getField('target'), $i= ProtectedAccessibilityFixture::construct(self::$fixture)); {
      $f->setAccessible(true);
      $f->set($i, 'Modified');
      Assert::equals('Modified', $f->get($i));
    }
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function writingProtectedStaticMember() {
    self::$fixture->getField('staticTarget')->set(null, 'Modified');
  }

  #[Test]
  public function writingProtectedStaticMemberFromSameClass() {
    Assert::equals('Modified', ProtectedAccessibilityFixture::writeStatic(self::$fixture));
  }

  #[Test]
  public function writingProtectedStaticMemberFromParentClass() {
    Assert::equals('Modified', ProtectedAccessibilityFixtureChild::writeStatic(self::$fixture));
  }

  #[Test]
  public function writingProtectedStaticMemberFromChildClass() {
    Assert::equals('Modified', ProtectedAccessibilityFixtureChild::writeStatic(self::$fixtureChild));
  }

  #[Test]
  public function writingProtectedStaticMemberMadeAccessible() {
    with ($f= self::$fixture->getField('staticTarget')); {
      $f->setAccessible(true);
      $f->set(null, 'Modified');
      Assert::equals('Modified', $f->get(null));
    }
  }
}