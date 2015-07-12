<?php namespace net\xp_framework\unittest\core\generics;

/**
 * Lookup map
 */
#[@generic(self= 'K, V', implements= ['K, V'])]
abstract class AbstractDictionary extends \lang\Object implements IDictionary {
  
  /**
   * Constructor
   *
   * @param   [:var] initial
   */
  public function __construct($initial= []) {
    foreach ($initial as $key => $value) {
      $this->put($key, $value);
    }
  }
}