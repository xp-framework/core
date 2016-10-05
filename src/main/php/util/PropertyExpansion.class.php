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
   * Register an expansion implementation
   *
   * @param  string $kind
   * @param  function(string, string: string) $func
   * @return self
   */
  public function expand($kind, callable $func) {
    $this->impl[$kind]= function($name, $default= null) use($kind, $func) {
      $expanded= $func($name);
      if (false === $expanded || null === $expanded) {
        if (null === $default) {
          throw new ElementNotFoundException('Cannot expand '.$kind.' '.$name);
        }
        return $default;
      } else {
        return $expanded;
      }
    };
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
        return $f($match[2], isset($match[3]) ? $match[3] : null);
      },
      $string
    );
  }
}