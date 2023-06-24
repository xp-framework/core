<?php namespace net\xp_framework\unittest\util;

use lang\{IllegalArgumentException, IllegalStateException};
use unittest\{Assert, Expect, Test};
use util\TimeSpan;

class TimeSpanTest {
  
  #[Test]
  public function newTimeSpan() {
    Assert::equals('0d, 2h, 1m, 5s', (new TimeSpan(7265))->toString());
  }

  #[Test]
  public function newNegativeTimeSpan() {
    Assert::equals('0d, 0h, 0m, 1s', (new TimeSpan(-1))->toString());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function wrongArguments() {
    new TimeSpan('2 days');
  }

  #[Test]
  public function add() {
    Assert::equals('0d, 2h, 1m, 5s', (new TimeSpan(3600))
      ->add(new TimeSpan(3600), new TimeSpan(60))
      ->add(new TimeSpan(5))->toString()
    );
  }
    
  #[Test]
  public function subtract() {
    Assert::equals('0d, 22h, 58m, 55s', (new TimeSpan(86400))
      ->substract(new TimeSpan(3600), new TimeSpan(60))
      ->substract(new TimeSpan(5))->toString()
    );
  }

  #[Test]
  public function subtractToZero() {
    Assert::equals(
      '0d, 0h, 0m, 0s', 
      (new TimeSpan(6100))->substract(new TimeSpan(6100))->toString()
    );
  }

  #[Test, Expect(IllegalStateException::class)]
  public function subtractToNegative() {
    (new TimeSpan(0))->substract(new TimeSpan(1));
  }

  #[Test]
  public function addAndSubstract() {
    Assert::equals('1d, 1h, 0m, 55s', (new TimeSpan(86400))
      ->add(new TimeSpan(3600), new TimeSpan(60))
      ->substract(new TimeSpan(5))->toString()
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function addWrongArguments() {
    (new TimeSpan(0))->add('2 days');
  }

  #[Test]
  public function fromSeconds() {
    Assert::equals('0d, 1h, 0m, 0s', TimeSpan::seconds(3600)->toString());
  }

  #[Test]
  public function fromMinutes() {
    Assert::equals('0d, 2h, 7m, 0s', TimeSpan::minutes(127)->toString());
  }
  
  #[Test]
  public function fromHours() {
    Assert::equals('1d, 3h, 0m, 0s', TimeSpan::hours(27)->toString());
  }

  #[Test]
  public function fromDays() {
    Assert::equals('40d, 0h, 0m, 0s', TimeSpan::days(40)->toString());
  }
  
  #[Test]
  public function fromWeeks() {
    Assert::equals('7d, 0h, 0m, 0s', TimeSpan::weeks(1)->toString());
  }

  #[Test]
  public function wholeValues() {
    $t= new TimeSpan(91865);
    Assert::equals(5, $t->getWholeSeconds(), 'wholeSeconds');
    Assert::equals(31, $t->getWholeMinutes(), 'wholeMinutes');
    Assert::equals(1, $t->getWholeHours(), 'wholeHours');
    Assert::equals(1, $t->getWholeDays(), 'wholeDays');
  }

  #[Test]
  public function formatSeconds() {
    Assert::equals('91865', (new TimeSpan(91865))->format('%s'));
  }

  #[Test]
  public function formatWholeSeconds() {
    Assert::equals('5', (new TimeSpan(91865))->format('%w'));
  }

  #[Test]
  public function formatMinutes() {
    Assert::equals('1531', (new TimeSpan(91865))->format('%m'));
  }

  #[Test]
  public function formatFloatMinutes() {
    Assert::equals('1531.08', (new TimeSpan(91865))->format('%M'));
  }

  #[Test]
  public function formatWholeMinutes() {
    Assert::equals('31', (new TimeSpan(91865))->format('%j'));
  }

  #[Test]
  public function formatHours() {
    Assert::equals('25', (new TimeSpan(91865))->format('%h'));
  }

  #[Test]
  public function formatFloatHours() {
    Assert::equals('25.52', (new TimeSpan(91865))->format('%H'));
  }

  #[Test]
  public function formatWholeHours() {
    Assert::equals('1', (new TimeSpan(91865))->format('%y'));
  }

  #[Test]
  public function formatDays() {
    Assert::equals('1', (new TimeSpan(91865))->format('%d'));
  }

  #[Test]
  public function formatFloatDays() {
    Assert::equals('1.06', (new TimeSpan(91865))->format('%D'));
  }

  #[Test]
  public function formatWholeDays() {
    Assert::equals('1', (new TimeSpan(91865))->format('%e'));
  }

  #[Test]
  public function format() {
    Assert::equals('1d1h', (new TimeSpan(91865))->format('%ed%yh'));
  }

  #[Test]
  public function formatPercent() {
    Assert::equals('%1d%1h%', (new TimeSpan(91865))->format('%%%ed%%%yh%%'));
  }

  #[Test]
  public function compareTwoEqualInstances() {
    Assert::equals(new TimeSpan(3600), new TimeSpan(3600));
  }

  #[Test]
  public function compareTwoUnequalInstances() {
    Assert::notEquals(new TimeSpan(3600), new TimeSpan(0));
  }
}