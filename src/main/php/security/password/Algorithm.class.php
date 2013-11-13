<?php namespace security\password;
 


/**
 * Defines an algorithm that calculates the strength of a password
 *
 * @purpose  Interface
 */
interface Algorithm {
  
  /**
   * Calculate the strength of a password
   *
   * @param   string password
   * @return  int
   */
  public function strengthOf($password);
}
