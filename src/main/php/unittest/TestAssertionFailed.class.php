<?php namespace unittest;

/**
 * Indicates a test failed
 *
 * @see   xp://unittest.TestFailure
 */
class TestAssertionFailed extends \lang\Object implements TestFailure {
  public
    $reason   = null,
    $test     = null,
    $elapsed  = 0.0;
    
  /**
   * Constructor
   *
   * @param   unittest.TestCase test
   * @param   unittest.AssertionFailedError reason
   * @param   float elapsed
   */
  public function __construct(TestCase $test, AssertionFailedError $reason, $elapsed) {
    $this->test= $test;
    $this->reason= $reason;
    $this->elapsed= $elapsed;
  }

  /**
   * Returns elapsed time
   *
   * @return  float
   */
  public function elapsed() {
    return $this->elapsed;
  }

  /**
   * Return a string representation of this class
   *
   * @return  string
   */
  public function toString() {
    return sprintf(
      "%s(test= %s, time= %.3f seconds) {\n  %s\n }",
      nameof($this),
      $this->test->getName(true),
      $this->elapsed,
      \xp::stringOf($this->reason, '  ')
    );
  }
}
