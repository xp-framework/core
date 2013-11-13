<?php namespace security\auth;



/**
 * Autenticates users against a property
 *
 * @purpose  Authenticator
 */
class PropertyAuthenticator extends \lang\Object implements Authenticator {
  public
    $users = null;

  /**
   * Constructor
   *
   * @param   util.Properties users
   */    
  public function __construct($prop) {
    $this->users= $prop;

  }
  
  /**
   * Authenticate a user
   *
   * @param   string user
   * @param   string pass
   * @return  bool
   */
  public function authenticate($user, $pass) {
    $user= $this->users->readSection(sprintf('user::%s', $user), null);
    return ($pass === $user['password']) ? true : false;
  }

} 
