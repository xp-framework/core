<?php namespace unittest\web;

/**
 * Represents a HTML input field
 *
 * @see   http://www.w3schools.com/TAGS/tag_input.asp
 * @see   xp://unittest.web.Field
 */
class InputField extends Field {

  /**
   * Get this field's value
   *
   * @return  string
   */
  public function getValue() {
    return $this->node->hasAttribute('value') ? iconv('utf-8', \xp::ENCODING, $this->node->getAttribute('value')) : null;
  }

  /**
   * Set this field's value
   *
   * @param   string value
   */
  public function setValue($value) {
    $this->node->setAttribute('value', iconv(\xp::ENCODING, 'utf-8', $value));
  }
}
