<?php namespace lang\unittest;

use lang\Generic;

#[Generic(self: 'V', implements: ['lang.Type, V'])]
abstract class AbstractTypeDictionary implements IDictionary {

}