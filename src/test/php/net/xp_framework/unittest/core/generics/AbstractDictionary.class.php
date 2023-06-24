<?php namespace net\xp_framework\unittest\core\generics;

#[Generic(self: 'K, V', implements: ['K, V'])]
abstract class AbstractDictionary implements IDictionary, Marker {
  
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