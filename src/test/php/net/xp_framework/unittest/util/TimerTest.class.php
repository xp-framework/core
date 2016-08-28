<?php namespace net\xp_framework\unittest\util;
 
use unittest\TestCase;
use util\profiling\Timer;
use util\Comparator;
use unittest\actions\RuntimeVersion;
use lang\{Error, IllegalArgumentException};

/**
 * Tests Timer class
 *
 * @see      xp://util.profiling.Timer
 */
class TimerTest extends TestCase {

  #[@test]
  public function can_create() {
    new Timer();
  }

  #[@test]
  public function elapsed_time_is_zero_before_timer_is_started() {
    $this->assertEquals(0.0, (new Timer())->elapsedTime());
  }

  #[@test]
  public function start_returns_timer() {
    $t= new Timer();
    $this->assertEquals($t, $t->start());
  }

  #[@test]
  public function stop_returns_timer() {
    $t= new Timer();
    $t->start();
    $this->assertEquals($t, $t->stop());
  }

  #[@test]
  public function calling_stop_before_start_does_not_raise_exception() {
    (new Timer())->stop();
  }

  #[@test]
  public function elapsed_time_after_50_milliseconds() {
    $fixture= (new Timer())->start();
    usleep(50 * 1000);
    $elapsed= $fixture->stop()->elapsedTime();
    $this->assertTrue($elapsed > 0.0, 'Elapsed time '.$elapsed.' should be greater than zero');
  }

  #[@test]
  public function elapsed_time_without_stop_after_50_milliseconds() {
    $fixture= (new Timer())->start();
    usleep(50 * 1000);
    $elapsed= $fixture->elapsedTime();
    $this->assertTrue($elapsed > 0.0, 'Elapsed time '.$elapsed.' should be greater than zero');
  }

  #[@test]
  public function elapsed_time_measured_after_50_milliseconds() {
    $fixture= Timer::measure(function() {
      usleep(100 * 1000);
    });
    $elapsed= $fixture->elapsedTime();
    $this->assertTrue($elapsed > 0.0, 'Elapsed time '.$elapsed.' should be greater than zero');
  }

  #[@test, @expect(Error::class)]
  public function not_callable_argument_passed_to_measure_7() {
    Timer::measure('@not-callable@');
  }
}
