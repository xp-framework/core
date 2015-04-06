<?php namespace unittest\web;



/**
 * Represents an option inside a select field
 *
 * @see      http://www.w3schools.com/TAGS/tag_option.asp
 * @see      xp://unittest.web.SelectField#getOptions
 */
class SelectOption extends \lang\Object {
  protected
    $form   = null,
    $node   = null;
  
  /**
   * Constructor
   *
   * @param   unittest.web.Form form owner form
   * @param   php.DOMNode node
   */
  public function __construct(\Form $form, \DOMNode $node) {
    $this->form= $form;
    $this->node= $node;
  }

  /**
   * Get this option's value
   *
   * @return  string
   */
  public function getValue() {
    return $this->node->hasAttribute('value') ? iconv('utf-8', \xp::ENCODING, $this->node->getAttribute('value')) : null;
  }

  /**
   * Set this option's value
   *
   * @param   string value
   */
  public function setValue($value) {
    $this->node->setAttribute('value', iconv(\xp::ENCODING, 'utf-8', $value));
  }

  /**
   * Get this option's text
   *
   * @return  string
   */
  public function getText() {
    return iconv('utf-8', \xp::ENCODING, $this->node->textContent);
  }

  /**
   * Get whether this option is selected
   *
   * @return  bool
   */
  public function isSelected() {
    return $this->node->hasAttribute('selected');
  }

  /**
   * Creates a string representation
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'{'.$this->form->getTest()->getDom()->saveXML($this->node).'}';
  }
}
