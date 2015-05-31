<?php namespace unittest;

/**
 * Indicates a test was ignored
 *
 * @see   xp://unittest.TestSkipped
 */
class TestNotRun extends \lang\Object implements TestSkipped {
  public
    $reason   = '',
    $test     = null;
    
  /**
   * Constructor
   *
   * @param   unittest.TestCase test
   * @param   string reason
   */
  public function __construct(TestCase $test, $reason) {
    $this->test= $test;
    $this->reason= $reason;
  }

  /**
   * Returns elapsed time
   *
   * @return  float
   */
  public function elapsed() {
    return 0.0;
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
