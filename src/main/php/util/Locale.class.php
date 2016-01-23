<?php namespace util;

/**
 * Locale
 * 
 * Usage [retreiving default locale]
 * ```php
 * $locale= Locale::getDefault();
 * var_dump($locale);
 * ```
 *
 * Usage [setting default locale]
 * ```php
 * Locale::setDefault(new Locale('de_DE'));
 * ```
 *
 * @see   http://ftp.ics.uci.edu/pub/ietf/http/related/iso639.txt
 * @see   http://userpage.chemie.fu-berlin.de/diverse/doc/ISO_3166.html
 * @see   http://groups.google.com/groups?threadm=DREPPER.96Aug8030605%40i44d2.ipd.info.uni-karlsruhe.de#link1
 * @test  xp://net.xp_framework.unittest.util.LocaleTest
 */
class Locale extends \lang\Object {
  public
    $lang     = '',
    $country  = '',
    $variant  = '';
  
  public
    $_str     = '';

  /**
   * Constructor
   *
   * @param   string lang 2-letter abbreviation of language
   * @param   string country 2-letter abbreviation of country
   * @param   string variant default ''
   */
  public function __construct($lang, $country= null, $variant= null) {
    if (1 === func_num_args()) {
      $this->_str= $lang;
      sscanf($this->_str, '%2s_%2s%s', $this->lang, $this->country, $this->variant);
    } else {
      $this->_str= $lang.'_'.$country.($variant ? '@'.$variant : '');
      $this->lang= $lang;
      $this->country= $country;
      $this->variant= $variant;
    }
  }
  
  /**
   * Get default locale
   *
   * @return  util.Locale
   */
  public static function getDefault() {
    return new self(('C' == ($locale= setlocale(LC_CTYPE, 0)) 
      ? 'en_US'
      : $locale
    ));
  }
  
  /**
   * Set default locale for this script
   *
   * @param   util.Locale locale
   * @throws  lang.IllegalArgumentException in case the locale is not available
   */
  public static function setDefault($locale) {
    if (false === setlocale(LC_ALL, $locale->toString())) {
      throw new \lang\IllegalArgumentException(sprintf(
        'Locale [lang=%s,country=%s,variant=%s] not available',
        $locale->lang, 
        $locale->country, 
        ltrim($locale->variant, '.@')
      ));
    }
  }

  /**
   * Get Language
   *
   * @return  string
   */
  public function getLanguage() {
    return $this->lang;
  }

  /**
   * Get Country
   *
   * @return  string
   */
  public function getCountry() {
    return $this->country;
  }

  /**
   * Get Variant
   *
   * @return  string
   */
  public function getVariant() {
    return $this->variant;
  }

  /**
   * Returns a hashcode for this object
   *
   * @return  string
   */
  public function hashCode() {
    return sprintf('%u', crc32($this->_str));
  }
  
  /**
   * Returns whether a given object is equal to this locale.
   *
   * @param   lang.Generic cmp
   * @return  bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $this->_str === $cmp->_str;
  }
  
  /**
   * Create string representation
   *
   * Examples:
   * <pre>
   * de_DE
   * en_US
   * de_DE@euro
   * de_DE.ISO8859-1
   * </pre>
   *
   * @return  string
   */
  public function toString() {
    return $this->_str;
  }
}
