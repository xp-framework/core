<?php namespace net\xp_framework\unittest\core\generics;

use lang\Generic;

#[Generic(self: 'V', implements: ['lang.Type, V'])]
abstract class AbstractTypeDictionary implements IDictionary {

}