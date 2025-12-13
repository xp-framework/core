<?php namespace util\unittest;

use DateInterval;
use lang\{IllegalArgumentException, IllegalStateException};
use test\{Assert, Expect, Test, Values};
use util\TimeSpan;

class TimeSpanTest {
  
  #[Test, Values([7265, 7265.0, 'PT2H1M5S', '2 hours 1 minute 5 seconds'])]
  public function span_from($arg) {
    Assert::equals('0d, 2h, 1m, 5s', (new TimeSpan($arg))->toString());
  }

  #[Test]
  public function negative_span() {
    Assert::equals('0d, 0h, 0m, 1s', (new TimeSpan(-1))->toString());
  }

  #[Test, Expect(IllegalArgumentException::class), Values([null, '', 'not a time span', false, true])]
  public function invalid_string_argument($arg) {
    new TimeSpan($arg);
  }

  #[Test, Expect(IllegalArgumentException::class), Values(['P1Y', 'P1M'])]
  public function unsupported_dateinterval($arg) {
    new TimeSpan(new DateInterval($arg));
  }

  #[Test]
  public function span_from_dateinterval() {
    Assert::equals('1d, 2h, 0m, 0s', (new TimeSpan(new DateInterval('P1DT2H')))->toString());
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
      ->substract(new TimeSpan(5))
      ->toString()
    );
  }

  #[Test]
  public function subtract_resulting_in_zero() {
    Assert::equals(
      '0d, 0h, 0m, 0s', 
      (new TimeSpan(6100))->substract(new TimeSpan(6100))->toString()
    );
  }

  #[Test, Expect(IllegalStateException::class)]
  public function subtract_may_not_result_in_negative_values() {
    (new TimeSpan(0))->substract(new TimeSpan(1));
  }

  #[Test]
  public function add_and_subtract() {
    Assert::equals('1d, 1h, 0m, 55s', (new TimeSpan(86400))
      ->add(new TimeSpan(3600), new TimeSpan(60))
      ->substract(new TimeSpan(5))
      ->toString()
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function incorrect_argument_to_add() {
    (new TimeSpan(0))->add('2 days');
  }

  #[Test]
  public function from_seconds() {
    Assert::equals('0d, 1h, 0m, 0s', TimeSpan::seconds(3600)->toString());
  }

  #[Test]
  public function from_minutes() {
    Assert::equals('0d, 2h, 7m, 0s', TimeSpan::minutes(127)->toString());
  }
  
  #[Test]
  public function from_hours() {
    Assert::equals('1d, 3h, 0m, 0s', TimeSpan::hours(27)->toString());
  }

  #[Test]
  public function from_days() {
    Assert::equals('40d, 0h, 0m, 0s', TimeSpan::days(40)->toString());
  }
  
  #[Test]
  public function from_weeks() {
    Assert::equals('7d, 0h, 0m, 0s', TimeSpan::weeks(1)->toString());
  }

  #[Test]
  public function whole_values() {
    $t= new TimeSpan(91865);
    Assert::equals(5, $t->getWholeSeconds(), 'wholeSeconds');
    Assert::equals(31, $t->getWholeMinutes(), 'wholeMinutes');
    Assert::equals(1, $t->getWholeHours(), 'wholeHours');
    Assert::equals(1, $t->getWholeDays(), 'wholeDays');
  }

  #[Test]
  public function format_seconds() {
    Assert::equals('91865', (new TimeSpan(91865))->format('%s'));
  }

  #[Test]
  public function format_whole_seconds() {
    Assert::equals('5', (new TimeSpan(91865))->format('%w'));
  }

  #[Test]
  public function format_minutes() {
    Assert::equals('1531', (new TimeSpan(91865))->format('%m'));
  }

  #[Test]
  public function format_float_minutes() {
    Assert::equals('1531.08', (new TimeSpan(91865))->format('%M'));
  }

  #[Test]
  public function format_whole_minutes() {
    Assert::equals('31', (new TimeSpan(91865))->format('%j'));
  }

  #[Test]
  public function format_hours() {
    Assert::equals('25', (new TimeSpan(91865))->format('%h'));
  }

  #[Test]
  public function format_float_hours() {
    Assert::equals('25.52', (new TimeSpan(91865))->format('%H'));
  }

  #[Test]
  public function format_whole_hours() {
    Assert::equals('1', (new TimeSpan(91865))->format('%y'));
  }

  #[Test]
  public function format_days() {
    Assert::equals('1', (new TimeSpan(91865))->format('%d'));
  }

  #[Test]
  public function format_float_days() {
    Assert::equals('1.06', (new TimeSpan(91865))->format('%D'));
  }

  #[Test]
  public function format_whole_days() {
    Assert::equals('1', (new TimeSpan(91865))->format('%e'));
  }

  #[Test]
  public function format_() {
    Assert::equals('1d1h', (new TimeSpan(91865))->format('%ed%yh'));
  }

  #[Test]
  public function format_percent() {
    Assert::equals('%1d%1h%', (new TimeSpan(91865))->format('%%%ed%%%yh%%'));
  }

  #[Test]
  public function compare_two_equal_instances() {
    Assert::equals(new TimeSpan(3600), new TimeSpan(3600));
  }

  #[Test]
  public function compare_two_differing_instances() {
    Assert::notEquals(new TimeSpan(3600), new TimeSpan(0));
  }
}