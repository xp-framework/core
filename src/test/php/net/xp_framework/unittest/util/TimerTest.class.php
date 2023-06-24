<?php namespace net\xp_framework\unittest\util;

use lang\{Error, IllegalArgumentException};
use unittest\Assert;
use unittest\actions\RuntimeVersion;
use unittest\{Expect, Test, TestCase};
use util\Comparator;
use util\profiling\Timer;

/**
 * Tests Timer class
 *
 * @see      xp://util.profiling.Timer
 */
class TimerTest {

  #[Test]
  public function can_create() {
    new Timer();
  }

  #[Test]
  public function elapsed_time_is_zero_before_timer_is_started() {
    Assert::equals(0.0, (new Timer())->elapsedTime());
  }

  #[Test]
  public function start_returns_timer() {
    $t= new Timer();
    Assert::equals($t, $t->start());
  }

  #[Test]
  public function stop_returns_timer() {
    $t= new Timer();
    $t->start();
    Assert::equals($t, $t->stop());
  }

  #[Test]
  public function calling_stop_before_start_does_not_raise_exception() {
    (new Timer())->stop();
  }

  #[Test]
  public function elapsed_time_after_50_milliseconds() {
    $fixture= (new Timer())->start();
    usleep(50 * 1000);
    $elapsed= $fixture->stop()->elapsedTime();
    Assert::true($elapsed > 0.0, 'Elapsed time '.$elapsed.' should be greater than zero');
  }

  #[Test]
  public function elapsed_time_without_stop_after_50_milliseconds() {
    $fixture= (new Timer())->start();
    usleep(50 * 1000);
    $elapsed= $fixture->elapsedTime();
    Assert::true($elapsed > 0.0, 'Elapsed time '.$elapsed.' should be greater than zero');
  }

  #[Test]
  public function elapsed_time_measured_after_50_milliseconds() {
    $fixture= Timer::measure(function() {
      usleep(100 * 1000);
    });
    $elapsed= $fixture->elapsedTime();
    Assert::true($elapsed > 0.0, 'Elapsed time '.$elapsed.' should be greater than zero');
  }

  #[Test, Expect(Error::class)]
  public function not_callable_argument_passed_to_measure_7() {
    Timer::measure('@not-callable@');
  }
}