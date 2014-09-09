<?php namespace unittest;

/**
 * The message for an assertion failure
 *
 * @see  xp://unittest.ComparisonFailedMessage
 */
interface AssertionFailedMessage {

  /**
   * Return formatted message - "expected ... but was .... using: ..."
   *
   * @return  string
   */
  public function format();
}