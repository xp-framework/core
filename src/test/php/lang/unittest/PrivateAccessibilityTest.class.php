<?php namespace lang\unittest;

use lang\{ClassLoader, IllegalAccessException, XPClass};
use test\{Assert, Before, Expect, Ignore, Test};

class PrivateAccessibilityTest {
  private static $fixture, $fixtureChild, $fixtureCtorChild;
  
  #[Before]
  public static function initializeClasses() {
    self::$fixture= XPClass::forName('lang.unittest.PrivateAccessibilityFixture');
    self::$fixtureChild= XPClass::forName('lang.unittest.PrivateAccessibilityFixtureChild');
    self::$fixtureCtorChild= XPClass::forName('lang.unittest.PrivateAccessibilityFixtureCtorChild');
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingPrivateConstructor() {
    self::$fixture->getConstructor()->newInstance([]);
  }

  #[Test]
  public function invokingPrivateConstructorFromSameClass() {
    Assert::instance(self::$fixture, PrivateAccessibilityFixture::construct(self::$fixture));
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingPrivateConstructorFromParentClass() {
    PrivateAccessibilityFixtureCtorChild::construct(self::$fixture);
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingPrivateConstructorFromChildClass() {
    PrivateAccessibilityFixtureCtorChild::construct(self::$fixtureChild);
  }

  #[Test]
  public function invokingPrivateConstructorMadeAccessible() {
    Assert::instance(self::$fixture, self::$fixture
      ->getConstructor()
      ->setAccessible(true)
      ->newInstance([])
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingPrivateMethod() {
    self::$fixture->getMethod('target')->invoke(PrivateAccessibilityFixture::construct(self::$fixture));
  }

  #[Test]
  public function invokingPrivateMethodFromSameClass() {
    Assert::equals('Invoked', PrivateAccessibilityFixture::invoke(self::$fixture));
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingPrivateMethodFromParentClass() {
    PrivateAccessibilityFixtureChild::invoke(self::$fixture);
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingPrivateMethodFromChildClass() {
    PrivateAccessibilityFixtureChild::invoke(self::$fixtureChild);
  }

  #[Test]
  public function invokingPrivateMethodMadeAccessible() {
    Assert::equals('Invoked', self::$fixture
      ->getMethod('target')
      ->setAccessible(true)
      ->invoke(PrivateAccessibilityFixture::construct(self::$fixture))
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingPrivateStaticMethod() {
    self::$fixture->getMethod('staticTarget')->invoke(null);
  }

  #[Test]
  public function invokingPrivateStaticMethodFromSameClass() {
    Assert::equals('Invoked', PrivateAccessibilityFixture::invokeStatic(self::$fixture));
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingPrivateStaticMethodFromParentClass() {
    PrivateAccessibilityFixtureChild::invokeStatic(self::$fixture);
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function invokingPrivateStaticMethodFromChildClass() {
    PrivateAccessibilityFixtureChild::invokeStatic(self::$fixtureChild);
  }

  #[Test]
  public function invokingPrivateStaticMethodMadeAccessible() {
    Assert::equals('Invoked', self::$fixture
      ->getMethod('staticTarget')
      ->setAccessible(true)
      ->invoke(null)
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function readingPrivateMember() {
    self::$fixture->getField('target')->get(PrivateAccessibilityFixture::construct(self::$fixture));
  }

  #[Test]
  public function readingPrivateMemberFromSameClass() {
    Assert::equals('Target', PrivateAccessibilityFixture::read(self::$fixture));
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function readingPrivateMemberFromParentClass() {
    PrivateAccessibilityFixtureChild::read(self::$fixture);
  }

  #[Test, Ignore('$this->getClass()->getField($field) does not yield private field declared in parent class')]
  public function readingPrivateMemberFromChildClass() {
    PrivateAccessibilityFixtureChild::read(self::$fixtureChild);
  }

  #[Test]
  public function readingPrivateMemberMadeAccessible() {
    Assert::equals('Target', self::$fixture
      ->getField('target')
      ->setAccessible(true)
      ->get(PrivateAccessibilityFixture::construct(self::$fixture))
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function readingPrivateStaticMember() {
    self::$fixture->getField('staticTarget')->get(null);
  }

  #[Test]
  public function readingPrivateStaticMemberFromSameClass() {
    Assert::equals('Target', PrivateAccessibilityFixture::readStatic(self::$fixture));
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function readingPrivateStaticMemberFromParentClass() {
    PrivateAccessibilityFixtureChild::readStatic(self::$fixture);
  }

  #[Test, Ignore('$this->getClass()->getField($field) does not yield private field declared in parent class')]
  public function readingPrivateStaticMemberFromChildClass() {
    PrivateAccessibilityFixtureChild::readStatic(self::$fixtureChild);
  }

  #[Test]
  public function readingPrivateStaticMemberMadeAccessible() {
    Assert::equals('Target', self::$fixture
      ->getField('staticTarget')
      ->setAccessible(true)
      ->get(null)
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function writingPrivateMember() {
    self::$fixture->getField('target')->set(PrivateAccessibilityFixture::construct(self::$fixture), null);
  }

  #[Test]
  public function writingPrivateMemberFromSameClass() {
    Assert::equals('Modified', PrivateAccessibilityFixture::write(self::$fixture));
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function writingPrivateMemberFromParentClass() {
    PrivateAccessibilityFixtureChild::write(self::$fixture);
  }

  #[Test, Ignore('$this->getClass()->getField($field) does not yield private field declared in parent class')]
  public function writingPrivateMemberFromChildClass() {
    PrivateAccessibilityFixtureChild::write(self::$fixtureChild);
  }

  #[Test]
  public function writingPrivateMemberMadeAccessible() {
    with ($f= self::$fixture->getField('target'), $i= PrivateAccessibilityFixture::construct(self::$fixture)); {
      $f->setAccessible(true);
      $f->set($i, 'Modified');
      Assert::equals('Modified', $f->get($i));
    }
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function writingPrivateStaticMember() {
    self::$fixture->getField('staticTarget')->set(null, 'Modified');
  }

  #[Test]
  public function writingPrivateStaticMemberFromSameClass() {
    Assert::equals('Modified', PrivateAccessibilityFixture::writeStatic(self::$fixture));
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function writingPrivateStaticMemberFromParentClass() {
    PrivateAccessibilityFixtureChild::writeStatic(self::$fixture);
  }

  #[Test, Ignore('$this->getClass()->getField($field) does not yield private field declared in parent class')]
  public function writingPrivateStaticMemberFromChildClass() {
    PrivateAccessibilityFixtureChild::writeStatic(self::$fixtureChild);
  }

  #[Test]
  public function writingPrivateStaticMemberMadeAccessible() {
    with ($f= self::$fixture->getField('staticTarget')); {
      $f->setAccessible(true);
      $f->set(null, 'Modified');
      Assert::equals('Modified', $f->get(null));
    }
  }
}