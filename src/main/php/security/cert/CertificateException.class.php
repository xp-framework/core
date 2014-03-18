<?php namespace security\cert;

/**
 * CertificateException
 *
 * @see      xp://security.cert.Certificate
 * @purpose  This exception indicates one of a variety of certificate problems.
 */
class CertificateException extends \lang\XPException {
  public
    $errors = [];
    
  /**
   * Constructor
   *
   * @param   string message
   * @param   string[] errors default []
   */
  public function __construct($message, $errors= []) {
    parent::__construct($message);
    $this->errors= $errors;
  }

  /**
   * Returns errors
   *
   * @return  string[] errors
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * Return compound message of this exception.
   *
   * @return  string
   */
  public function compoundMessage() {
    return sprintf(
      "Exception %s (%s) {\n".
      "  %s\n".
      "}\n",
      $this->getClassName(),
      $this->message,
      implode("\n  @", $this->errors)
    );
  }
}
