<?php namespace lang\unittest;

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