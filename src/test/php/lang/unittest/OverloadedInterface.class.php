<?php namespace lang\unittest;

use lang\Overloaded;

interface OverloadedInterface {
  
  /** Overloaded method */
  #[Overloaded(signatures: [['string'], ['string', 'string']])]
  public function overloaded();
}