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
  public function coin_is_an_enum() {
    $this->assertTrue(XPClass::forName(Coin::class)->isEnum());
  }
  
  #[Test]
  public function operation_is_an_enum() {
    $this->assertTrue(XPClass::forName(Operation::class)->isEnum());
  }

  #[Test, Action(eval: 'new VerifyThat(fn() => class_exists("ReflectionEnum", false))')]
  public function sortorder_is_an_enum() {
    $this->assertTrue(XPClass::forName(SortOrder::class)->isEnum());
  }

  #[Test]
  public function this_is_not_an_enum() {
    $this->assertFalse(typeof($this)->isEnum());
  }

  #[Test]
  public function enum_base_class_is_abstract() {
    $this->assertAbstract(XPClass::forName(Enum::class)->getModifiers());
  }

  #[Test]
  public function operation_enum_is_abstract() {
    $this->assertAbstract(XPClass::forName(Operation::class)->getModifiers());
  }

  #[Test]
  public function coin_enum_is_not_abstract() {
    $this->assertNotAbstract(XPClass::forName(Coin::class)->getModifiers());
  }

  #[Test]
  public function coin_member_is_instance_of_coin() {
    $this->assertInstanceOf(Coin::class, Coin::$penny);
  }

  #[Test]
  public function operation_member_is_instance_of_operation() {
    $this->assertInstanceOf(Operation::class, Operation::$plus);
  }

  #[Test]
  public function enum_members_are_not_abstract() {
    $this->assertNotAbstract(typeof(Coin::$penny)->getModifiers());
    $this->assertNotAbstract(typeof(Operation::$plus)->getModifiers());
  }

  #[Test]
  public function coin_values() {
    $this->assertEquals(
      [Coin::$penny, Coin::$nickel, Coin::$dime, Coin::$quarter],
      Coin::values()
    );
  }

  #[Test]
  public function operation_values() {
    $this->assertEquals(
      [Operation::$plus, Operation::$minus, Operation::$times, Operation::$divided_by],
      Operation::values()
    );
  }

  #[Test]
  public function nickel_coin_name() {
    $this->assertEquals('nickel', Coin::$nickel->name());
  }

  #[Test]
  public function nickel_coin_value() {
    $this->assertEquals(2, Coin::$nickel->value());
  }

  #[Test]
  public function string_representation() {
    $this->assertEquals('dime', Coin::$dime->toString());
  }

  #[Test]
  public function same_coins_are_equal() {
    $this->assertEquals(Coin::$quarter, Coin::$quarter);
  }

  #[Test]
  public function different_coins_are_not_equal() {
    $this->assertNotEquals(Coin::$penny, Coin::$quarter);
  }

  #[Test, Expect(CloneNotSupportedException::class)]
  public function enum_members_cannot_be_cloned() {
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

  #[Test, Action(eval: 'new VerifyThat(fn() => class_exists("ReflectionEnum", false))')]
  public function valueOf_sortorder_enum() {
    $this->assertEquals(
      SortOrder::ASC,
      Enum::valueOf(SortOrder::class, 'ASC')
    );
  }

  #[Test, Expect(IllegalArgumentException::class), Action(eval: 'new VerifyThat(fn() => class_exists("ReflectionEnum", false))')]
  public function valueOf_nonexistant_sortorder_enum() {
    Enum::valueOf(SortOrder::class, 'ESC');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function valueOf_nonexistant() {
    Enum::valueOf(XPClass::forName(Coin::class), '@@DOES_NOT_EXIST@@');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function valueOf_non_enum() {
    Enum::valueOf(self::class, 'irrelevant');
  }

  #[Test]
  public function valueOf_abstract_enum() {
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
  public function valuesOf_abstract_enum() {
    $this->assertEquals(
      [Operation::$plus, Operation::$minus, Operation::$times, Operation::$divided_by],
      Enum::valuesOf(XPClass::forName(Operation::class))
    );
  }

  #[Test, Action(eval: 'new VerifyThat(fn() => class_exists("ReflectionEnum", false))')]
  public function valuesOf_sortorder_enum() {
    $this->assertEquals(
      [SortOrder::ASC, SortOrder::DESC],
      Enum::valuesOf(XPClass::forName(SortOrder::class))
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function valuesOf_non_enum() {
    Enum::valuesOf(self::class);
  }

  #[Test]
  public function plus_operation() {
    $this->assertEquals(2, Operation::$plus->evaluate(1, 1));
  }

  #[Test]
  public function minus_operation() {
    $this->assertEquals(0, Operation::$minus->evaluate(1, 1));
  }

  #[Test]
  public function times_operation() {
    $this->assertEquals(21, Operation::$times->evaluate(7, 3));
  }

  #[Test]
  public function dividedBy_operation() {
    $this->assertEquals(5, Operation::$divided_by->evaluate(10, 2));
  }
  
  #[Test]
  public function static_member_not_in_enum_valuesOf() {
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Enum::valuesOf(XPClass::forName('net.xp_framework.unittest.core.Profiling'))
    );
  }

  #[Test]
  public function static_member_not_in_values() {
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Profiling::values()
    );
  }
  
  #[Test, Expect(IllegalArgumentException::class)]
  public function static_member_not_acceptable_in_valueOf() {
    Enum::valueOf(XPClass::forName('net.xp_framework.unittest.core.Profiling'), 'fixture');
  }

  #[Test]
  public function static_member_with_enum_type_not_in_enum_valuesOf() {
    Profiling::$fixture= Coin::$penny;
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Enum::valuesOf(XPClass::forName('net.xp_framework.unittest.core.Profiling'))
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function static_member_with_enum_type_not_in_enum_values() {
    Profiling::$fixture= Coin::$penny;
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Profiling::values()
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function static_object_member_not_in_enum_valuesOf() {
    Profiling::$fixture= $this;
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Enum::valuesOf(XPClass::forName('net.xp_framework.unittest.core.Profiling'))
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function static_object_member_not_in_enum_values() {
    Profiling::$fixture= $this;
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Profiling::values()
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function static_primitive_member_not_in_enum_valuesOf() {
    Profiling::$fixture= [$this, $this->name];
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Enum::valuesOf(XPClass::forName('net.xp_framework.unittest.core.Profiling'))
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function static_primitive_member_not_in_enum_values() {
    Profiling::$fixture= [$this, $this->name];
    $this->assertEquals(
      [Profiling::$INSTANCE, Profiling::$EXTENSION],
      Profiling::values()
    );
    Profiling::$fixture= null;
  }

  #[Test]
  public function enum_values_method() {
    $this->assertEquals(
      [Weekday::$MON, Weekday::$TUE, Weekday::$WED, Weekday::$THU, Weekday::$FRI, Weekday::$SAT, Weekday::$SUN],
      Weekday::values()
    );
  }

  #[Test]
  public function enum_value_initialized_to_declaration() {
    $this->assertEquals(1, Weekday::$MON->ordinal());
  }
}