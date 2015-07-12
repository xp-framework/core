<?php namespace unittest\web;

/**
 * HTML field types enumeration
 *
 * @see   xp://unittest.web.Form#getFields
 */
abstract class Fields extends \lang\Enum {
  public static $INPUT, $SELECT, $TEXTAREA;

  static function __static() {
    self::$INPUT= newinstance(__CLASS__, [0, 'INPUT'], '{
      static function __static() { }
      
      public function newInstance($form, $node) {
        return new InputField($form, $node);
      }
    }');
    self::$SELECT= newinstance(__CLASS__, [1, 'SELECT'], '{
      static function __static() { }
      
      public function newInstance($form, $node) {
        return new SelectField($form, $node);
      }
    }');
    self::$TEXTAREA= newinstance(__CLASS__, [2, 'TEXTAREA'], '{
      static function __static() { }
      
      public function newInstance($form, $node) {
        return new TextAreaField($form, $node);
      }
    }');
  }
  
  /**
   * Creates a new instance of this field type
   *
   * @param   unittest.web.Form form
   * @param   php.DOMNode node
   * @return  unittest.web.Field
   */
  public abstract function newInstance($test, $node);

  /**
   * Return a field type
   *
   * @return  unittest.web.Fields
   */
  public static function forTag($type) {
    return parent::valueOf(\lang\XPClass::forName('unittest.web.Fields'), strtoupper($type));
  }
}
