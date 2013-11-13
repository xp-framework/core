<?php namespace unittest;

/**
 * Outcome from a test
 *
 */
interface TestOutcome {

  /**
   * Returns elapsed time
   *
   * @return  float
   */
  public function elapsed();

}
