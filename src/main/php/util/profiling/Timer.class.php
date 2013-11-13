<?php namespace util\profiling;
 
/**
 * The Timer class provides a simple timer
 *
 * <code>
 *   $p= new Timer();
 *   $p->start();
 *   // ... code you want profiled
 *   $p->stop();
 *   var_dump($p->elapsedTime());
 * </code>
 *
 * @test     xp://net.xp_framework.unittest.util.TimerTest
 * @purpose  Provide a simple profiling timer
 */
class Timer extends \lang\Object {
  public
    $start = 0.0,
    $stop  = 0.0;
    
  /**
   * Start the timer
   *
   */
  public function start() {
    $this->start= microtime(true);
  }
  
  /**
   * Stop the timer
   *
   */
  public function stop() {
    $this->stop= microtime(true);
  }

  /**
   * Measure a closure
   *
   * @see    http://php.net/manual/en/language.types.callable.php
   * @param  var block a callable
   * @return self
   * @throws lang.IllegalArgumentException when block is not callable
   */
  public static function measure($block) {
    if (!is_callable($block)) {
      throw new \lang\IllegalArgumentException('Cannot call '.\xp::stringOf($block));
    }

    $self= new self();
    $self->start= microtime(true);
    $block();
    $self->stop= microtime(true);
    return $self;
  }

  /**
   * Retrieve elapsed time
   *
   * @return  float seconds elapsed
   */
  public function elapsedTime() {
    return $this->stop - $this->start;
  }
}
