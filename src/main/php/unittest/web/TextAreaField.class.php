<?php namespace unittest\web;





/**
 * Represents a HTML textarea field
 *
 * @see      http://www.w3schools.com/TAGS/tag_textarea.asp
 * @see      xp://unittest.web.Field
 * @purpose  Value object
 */
class TextAreaField extends Field {

  /**
   * Get this field's value
   *
   * @return  string
   */
  public function getValue() {
    return iconv('utf-8', \xp::ENCODING, $this->node->textContent);
  }

  /**
   * Set this field's value
   *
   * @param   string value
   */
  public function setValue($value) {
    $this->node->textContent= iconv(\xp::ENCODING, 'utf-8', $value);
  }
}
