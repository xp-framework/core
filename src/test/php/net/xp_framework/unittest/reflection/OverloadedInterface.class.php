<?php namespace net\xp_framework\unittest\reflection;

use lang\Overloaded;

interface OverloadedInterface {
  
  /** Overloaded method */
  #[Overloaded(signatures: [['string'], ['string', 'string']])]
  public function overloaded();
}