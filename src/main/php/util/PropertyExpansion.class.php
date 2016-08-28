<?php namespace util;

use lang\ElementNotFoundException;
use lang\FormatException;

/**
 * Expands variables inside property files.
 *
 * @see  xp://util.Properties
 */
class PropertyExpansion {
  private $impl= [];

  /**
   * Creates an instance 
   */
  public function __construct() {
    $this->impl['env']= function($name, $default= null) {
      if (false === ($value= getenv($name))) {
        if (null === ($value= $default)) {
          throw new ElementNotFoundException('Environment variable "'.$name.'" doesn\'t exist');
        }
      }
      return $value;
    };
  }

  /**
   * Register an expansion implementation
   *
   * @param  string $name
   * @param  function(string, string): string $impl
   * @return self
   */
  public function expand($name, $impl) {
    $this->impl[$name]= $impl;
    return $this;
  }

  /**
   * Expand strings
   *
   * @param  string $string
   * @return string
   */
  public function in($string) {
    return preg_replace_callback(
      '/\$\{([^.}]*)\.([^}|]*)(?:\|([^}]*))?\}/',
      function($match) {
        if (!isset($this->impl[$match[1]])) {
          throw new FormatException('Unknown expansion type in '.$match[0]);
        }

        $f= $this->impl[$match[1]];
        return $f($match[2], $match[3] ?? null);
      },
      $string
    );
  }
}