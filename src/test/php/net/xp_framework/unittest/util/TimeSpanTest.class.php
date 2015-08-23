<?php namespace net\xp_framework\unittest\util;

use unittest\TestCase;
use util\TimeSpan;
use lang\IllegalArgumentException;
use lang\IllegalStateException;

/**
 * TestCase
 *
 * @see      xp://util.TimeSpan
 */
class TimeSpanTest extends TestCase {
  
  #[@test]
  public function newTimeSpan() {
    $this->assertEquals('0d, 2h, 1m, 5s', (new TimeSpan(7265))->toString());
  }

  #[@test]
  public function newNegativeTimeSpan() {
    $this->assertEquals('0d, 0h, 0m, 1s', (new TimeSpan(-1))->toString());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function wrongArguments() {
    new TimeSpan('2 days');
  }

  #[@test]
  public function add() {
    $this->assertEquals('0d, 2h, 1m, 5s', (new TimeSpan(3600))
      ->add(new TimeSpan(3600), new TimeSpan(60))
      ->add(new TimeSpan(5))->toString()
    );
  }
    
  #[@test]
  public function subtract() {
    $this->assertEquals('0d, 22h, 58m, 55s', (new TimeSpan(86400))
      ->substract(new TimeSpan(3600), new TimeSpan(60))
      ->substract(new TimeSpan(5))->toString()
    );
  }

  #[@test]
  public function subtractToZero() {
    $this->assertEquals(
      '0d, 0h, 0m, 0s', 
      (new TimeSpan(6100))->substract(new TimeSpan(6100))->toString()
    );
  }

  #[@test, @expect(IllegalStateException::class)]
  public function subtractToNegative() {
    (new TimeSpan(0))->substract(new TimeSpan(1));
  }

  #[@test]
  public function addAndSubstract() {
    $this->assertEquals('1d, 1h, 0m, 55s', (new TimeSpan(86400))
      ->add(new TimeSpan(3600), new TimeSpan(60))
      ->substract(new TimeSpan(5))->toString()
    );
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function addWrongArguments() {
    (new TimeSpan(0))->add('2 days');
  }

  #[@test]
  public function fromSeconds() {
    $this->assertEquals('0d, 1h, 0m, 0s', TimeSpan::seconds(3600)->toString());
  }

  #[@test]
  public function fromMinutes() {
    $this->assertEquals('0d, 2h, 7m, 0s', TimeSpan::minutes(127)->toString());
  }
  
  #[@test]
  public function fromHours() {
    $this->assertEquals('1d, 3h, 0m, 0s', TimeSpan::hours(27)->toString());
  }

  #[@test]
  public function fromDays() {
    $this->assertEquals('40d, 0h, 0m, 0s', TimeSpan::days(40)->toString());
  }
  
  #[@test]
  public function fromWeeks() {
    $this->assertEquals('7d, 0h, 0m, 0s', TimeSpan::weeks(1)->toString());
  }

  #[@test]
  public function wholeValues() {
    $t= new TimeSpan(91865);
    $this->assertEquals(5, $t->getWholeSeconds(), 'wholeSeconds');
    $this->assertEquals(31, $t->getWholeMinutes(), 'wholeMinutes');
    $this->assertEquals(1, $t->getWholeHours(), 'wholeHours');
    $this->assertEquals(1, $t->getWholeDays(), 'wholeDays');
  }

  #[@test]
  public function formatSeconds() {
    $this->assertEquals('91865', (new TimeSpan(91865))->format('%s'));
  }

  #[@test]
  public function formatWholeSeconds() {
    $this->assertEquals('5', (new TimeSpan(91865))->format('%w'));
  }

  #[@test]
  public function formatMinutes() {
    $this->assertEquals('1531', (new TimeSpan(91865))->format('%m'));
  }

  #[@test]
  public function formatFloatMinutes() {
    $this->assertEquals('1531.08', (new TimeSpan(91865))->format('%M'));
  }

  #[@test]
  public function formatWholeMinutes() {
    $this->assertEquals('31', (new TimeSpan(91865))->format('%j'));
  }

  #[@test]
  public function formatHours() {
    $this->assertEquals('25', (new TimeSpan(91865))->format('%h'));
  }

  #[@test]
  public function formatFloatHours() {
    $this->assertEquals('25.52', (new TimeSpan(91865))->format('%H'));
  }

  #[@test]
  public function formatWholeHours() {
    $this->assertEquals('1', (new TimeSpan(91865))->format('%y'));
  }

  #[@test]
  public function formatDays() {
    $this->assertEquals('1', (new TimeSpan(91865))->format('%d'));
  }

  #[@test]
  public function formatFloatDays() {
    $this->assertEquals('1.06', (new TimeSpan(91865))->format('%D'));
  }

  #[@test]
  public function formatWholeDays() {
    $this->assertEquals('1', (new TimeSpan(91865))->format('%e'));
  }

  #[@test]
  public function format() {
    $this->assertEquals('1d1h', (new TimeSpan(91865))->format('%ed%yh'));
  }

  #[@test]
  public function formatPercent() {
    $this->assertEquals('%1d%1h%', (new TimeSpan(91865))->format('%%%ed%%%yh%%'));
  }

  #[@test]
  public function compareTwoEqualInstances() {
    $this->assertEquals(new TimeSpan(3600), new TimeSpan(3600));
  }

  #[@test]
  public function compareTwoUnequalInstances() {
    $this->assertNotEquals(new TimeSpan(3600), new TimeSpan(0));
  }
}
