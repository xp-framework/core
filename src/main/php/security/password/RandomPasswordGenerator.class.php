<?php namespace security\password;

/**
 * Generates random password
 *
 * TODO: allow generation of customer-friendly passwords (no S/5, 1/l, ...)
 * @deprecated
 */
class RandomPasswordGenerator extends \lang\Object {
  public
    $lc= 'abcdefghijklmnopqrstuvwxyz',
    $uc= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    $sc= '_-=+!?#@$%^&*()[]{}/:;.,<>',
    $nc= '0123456789',
    $chars= '',
    $length= 8;

  /**
   * Constructor
   * type can contain the following characters:
   *  'c' to include capitals
   *  'n' to include numbers
   *  's' to include special characters
   *
   * @param   int length default 8
   * @param   string type default NULL
   */
  public function __construct($length= 8, $type= null) {
    $this->length= $length;
    if (null === $type) {
      $this->chars= $this->lc.$this->uc.$this->sc.$this->nc;
    } else {
      $this->chars= $this->lc;
      if (false !== strpos($type, 'c')) {
        $this->chars.= $this->uc;
      }
      if (false !== strpos($type, 'n')) {
        $this->chars.= $this->nc;
      }
      if (false !== strpos($type, 's')) {
        $this->chars.= $this->sc;
      }
    }
  }

  /**
   * Define a string of characters of which the password will be generated
   *
   * @param   string chars
   */
  public function setChars($chars) {
    $this->chars= $chars;
  }

  /**
   * Generate
   *
   * @return  string
   */
  public function generate() {
    $pass= '';
    for ($i= 0; $i< $this->length; $i++) {
      // $temp= str_shuffle($this->chars);
      // $pass.= $temp[0];
      $pass.= $this->chars[mt_rand(0, strlen($this->chars))];
    }
    return $pass;
  }
}
