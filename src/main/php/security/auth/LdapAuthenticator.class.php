<?php namespace security\auth;

use peer\ldap\LDAPClient;
use security\crypto\UnixCrypt;


/**
 * Autenticates users against LDAP
 *
 * @purpose  Authenticator
 */
class LdapAuthenticator extends \lang\Object implements Authenticator {
  public
    $lc     = null,
    $basedn = '';

  /**
   * Constructor
   *
   * @param   peer.ldap.LDAPClient lc
   * @param   string basedn
   */
  public function __construct($lc, $basedn) {
    $this->lc= $lc;
    $this->basedn= $basedn;
  }

  /**
   * Authenticate a user
   *
   * @param   string user
   * @param   string pass
   * @return  bool
   */
  public function authenticate($user, $pass) {
    try {
      $r= $this->lc->search($this->basedn, '(uid='.$user.')');
    } catch (\peer\ldap\LDAPException $e) {
      throw new \AuthenticatorException(sprintf(
        'Authentication failed (#%d: "%s")', 
        $e->getErrorCode(),
        $e->getMessage()
      ), $e);
    } catch (\peer\ConnectException $e) {
      throw new \AuthenticatorException(sprintf(
        'Authentication failed (<connect>: "%s")', 
        $e->getMessage()
      ), $e);
    }
    
    // Check return, we must find a distinct user
    if (1 != $r->numEntries()) return false;
    
    $entry= $r->getNextEntry();
    return UnixCrypt::matches($entry->getAttribute('userpassword', 0), $pass);
  }
} 
