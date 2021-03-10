<?php namespace net\xp_framework\unittest\core;

use lang\reflect\Modifiers;
use lang\{CloneNotSupportedException, Enum, Error, IllegalArgumentException, XPClass, ClassLoader};
use unittest\actions\{RuntimeVersion, VerifyThat};
use unittest\{Action, Expect, Test};

/**
 * TestCase for enumerations
 *
 * @see   xp://net.xp_framework.unittest.core.Coin
 * @see   xp://net.xp_framework.unittest.core.Operation
 * @see   xp://net.xp_framework.unittest.core.Weekday
 * @see   xp://lang.Enum
 * @see   xp://lang.XPClass#isEnum
 * @see   http://xp-framework.net/rfc/0132
 */
class EnumTest extends \unittest\TestCase {

  /**
   * Asserts given modifiers contain abstract
   *
   * @param  int $modifiers
   * @return void
   * @throws unittest.AssertionFailedError
   */
  protected function assertAbstract($modifiers) {
    $this->assertTrue(
      Modifiers::isAbstract($modifiers), 
      implode(' | ', Modifiers::namesOf($modifiers))
    );
  }

  /**
   * Asserts given modifiers do not contain abstract
   *
   * @param  int $modifiers
   * @return void
   * @throws unittest.AssertionFailedError
   */
  protected function assertNotAbstract($modifiers) {
    $this->assertFalse(
      Modifiers::isAbstract($modifiers), 
      implode(' | ', Modifiers::namesOf($modifiers))
    );
  }

  #[Test]
  public function coinIsAnEnums() {
    $this->assertTrue(XPClass::forName(Coin::class)->isEnum());
  }
  
  #[Test]
  public function operationIsAnEnums() {
    $this->assertTrue(XPClass::forName(Operation::class)->isEnum());
  }

  #[Test, Action(eval: 'new VerifyThat(fn() => class_exists("ReflectionEnum", false))')]
  public function sortorder_is_enum() {
    $this->assertTrue(XPClass::forName(SortOrder::class)->isEnum());
  }

  #[Test]
  public function thisIsNotAnEnum() {
    $this->assertFalse(typeof($this)->isEnum());
  }

  #[Test]
  public function enumBaseClassIsAbstract() {
    $this->assertAbstract(XPClass::forName(Enum::class)->getModifiers());
  }

  #[Test]
  public function operationEnumIsAbstract() {
    $this->assertAbstract(XPClass::forName(Operation::class)->getModifiers());
  }

  #[Test]
  public function coinEnumIsNotAbstract() {
    $this->assertNotAbstract(XPClass::forName(Coin::class)->getModifiers());
  }

  #[Test]
  public function coinMemberAreSameClass() {
    $this->assertInstanceOf(Coin::class, Coin::$penny);
  }

  #[Test]
  public function operationMembersAreSubclasses() {
    $this->assertInstanceOf(Operation::class, Operation::$plus);
  }

  #[Test]
  public function enumMembersAreNotAbstract() {
    $this->assertNotAbstract(typeof(Coin::$penny)->getModifiers());
    $this->assertNotAbstract(typeof(Operation::$plus)->getModifiers());
  }

  #[Test]
  public function coinValues() {
    $this->assertEquals(
      [Coin::$penny, Coin::$nickel, Coin::$dime, Coin::$quarter],
      Coin::values()
    );
  }

  #[Test]
  public function operationValues() {
    $this->assertEquals(
      [Operation::$plus, Operation::$minus, Operation::$times, Operation::$divided_by],
      Operation::values()
    );
  }

  #[Test]
  public function pennyCoinClass() {
    $this->assertInstanceOf(Coin::class, Coin::$penny);
  }

  #[Test]
  public function nickelCoinName() {
    $this->assertEquals('nickel', Coin::$nickel->name());
  }

  #[Test]
  public function nickelCoinValue() {
    $this->assertEquals(2, Coin::$nickel->value());
  }

  #[Test]
  public function stringRepresentation() {
    $this->assertEquals('dime', Coin::$dime->toString());
  }

  #[Test]
  public function sameCoinsAreEqual() {
    $this->assertEquals(Coin::$quarter, Coin::$quarter);
  }

  #[Test]
  public function differentCoinsAreNotEqual() {
    $this->assertNotEquals(Coin::$penny, Coin::$quarter);
  }

  #[Test, Expect(CloneNotSupportedException::class)]
  public function enumMembersAreNotCloneable() {
    clone Coin::$penny;
  }

  #[Test]
  public function valueOf() {
    $this->assertEquals(
      Coin::$penny, 
      Enum::valueOf(XPClass::forName(Coin::class), 'penny')
    );
  }

  #[Test]
  public function valueOf_string() {
    $this->assertEquals(
      Coin::$penny, 
      Enum::valueOf(Coin::class, 'penny')
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function valueOfNonExistant() {
    Enum::valueOf(XPClass::forName(Coin::class), '@@DOES_NOT_EXIST@@');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function valueOfNonEnum() {
    Enum::valueOf(self::class, 'irrelevant');
  }

  #[Test]
  public function valueOfAbstractEnum() {
    $this->assertEquals(
      Operation::$plus, 
      Enum::valueOf(XPClass::forName(Operation::class), 'plus')
    );
  }

  #[Test]
  public function valuesOf() {
    $this->assertEquals(
      [Coin::$penny, Coin::$nickel, Coin::$dime, Coin::$quarter],
      Enum::valuesOf(XPClass::forName(Coin::class))
    );
  }

  #[Test]
  public function valuesOf_string() {
    $this->assertEquals(
      [Coin::$penny, Coin::$nickel, Coin::$dime, Coin::$quarter],
      Enum::valuesOf(Coin::class)
    );
  }

  #[Test]
  public function valuesOfAbstractEnum() {
    $this->assertEquals(
      [Operation::$plus, Operation::$minus, Operation::$times, Operation::$divided_by],
      Enum::valuesOf(XPClass::forName(Operation::class))
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function valuesOfNonEnum() {
    Enum::valuesOf(self::class);
  }

  #[Test]
  public function plusOperation() {
    $this->assertEquals(2, Operation::$plus->evaluate(1, 1));
  }

  #[Test]
  public function minusOperation() {
    $this->assertEquals(0, Operation::$minus->evaluate(1, 1));
  }

  #[Test]
  public function timesOperation() {
    $this->assertEquals(21, Operation::$times->evaluate(7, 3));
  }

  #[Test]
  public function dividedByOperation() {
    $this->assertEquals(5, Operation::$divided_by->evaluate(10, 2));
  }
  
  #[Test]
  public function staticMemberNotInEnumValuesOf() {
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Enum::valuesOf(XPClass::forName('net.xp_framework.unittest.core.Profiling'))
    );
  }

  #[Test]
  public function staticMemberNotInValues() {
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Profiling::values()
    );
  }
  
  #[Test, Expect(IllegalArgumentException::class)]
  public function staticMemberNotWithEnumValueOf() {
    Enum::valueOf(XPClass::forName('net.xp_framework.unittest.core.Profiling'), 'fixture');
  }

  #[Test]
  public function staticEnumMemberNotInEnumValuesOf() {
    Profiling::$fixture= Coin::$penny;
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Enum::valuesOf(XPClass::forName('net.xp_framework.unittest.core.Profiling'))
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function staticEnumMemberNotInValues() {
    Profiling::$fixture= Coin::$penny;
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Profiling::values()
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function staticObjectMemberNotInEnumValuesOf() {
    Profiling::$fixture= $this;
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Enum::valuesOf(XPClass::forName('net.xp_framework.unittest.core.Profiling'))
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function staticObjectMemberNotInValues() {
    Profiling::$fixture= $this;
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Profiling::values()
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function staticPrimitiveMemberNotInEnumValuesOf() {
    Profiling::$fixture= [$this, $this->name];
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Enum::valuesOf(XPClass::forName('net.xp_framework.unittest.core.Profiling'))
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function staticPrimitiveMemberNotInValues() {
    Profiling::$fixture= [$this, $this->name];
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Profiling::values()
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function enumValuesMethodProvided() {
    $this->assertEquals(
      [Weekday::$MON, Weekday::$TUE, Weekday::$WED, Weekday::$THU, Weekday::$FRI, Weekday::$SAT, Weekday::$SUN],
      Weekday::values()
    );
  }

  #[Test]
  public function enumValueInitializedToDeclaration() {
    $this->assertEquals(1, Weekday::$MON->ordinal());
  }
}