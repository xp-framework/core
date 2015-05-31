<?php namespace peer;

/**
 * Indicate an error occured during authentication
 *
 * @see      xp://peer.SocketException
 */
class AuthenticationException extends SocketException {
  public
    $user = '',
    $pass = '';

  /**
   * Constructor
   *
   * @param   string message
   * @param   string user
   * @param   string pass default ''
   */
  public function __construct($message, $user, $pass= '') {
    parent::__construct($message);
    $this->user= $user;
    $this->pass= $pass;
  }

  /**
   * Get User
   *
   * @return  string
   */
  public function getUser() {
    return $this->user;
  }

  /**
   * Get Pass
   *
   * @return  string
   */
  public function getPass() {
    return $this->pass;
  }
}
