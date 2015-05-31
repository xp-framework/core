<?php namespace unittest\web;

use peer\http\HttpConstants;

/**
 * Represents a HTML form
 *
 * @see   xp://unittest.web.WebTestCase#getForm
 */
class Form extends \lang\Object {
  protected
    $test   = null,
    $node   = null,
    $fields = null;
  
  /**
   * Constructor
   *
   * @param   unittest.web.WebTestCase case
   * @param   php.DOMNode node
   */
  public function __construct(WebTestCase $test, \DOMNode $node) {
    $this->test= $test;
    $this->node= $node;
  }

  /**
   * Get test
   *
   * @return  unittest.web.WebTestCase
   */
  public function getTest() {
    return $this->test;
  }
  
  /**
   * Get form action
   *
   * @return  string
   */
  public function getAction() {
    $action= $this->node->getAttribute('action');
    return $action ? $action : $this->test->getBase();
  }

  /**
   * Get form method
   *
   * @return  string
   */
  public function getMethod() {
    $method= $this->node->getAttribute('method');
    return $method ? $method : HttpConstants::GET;
  }

  /**
   * Get fields. Lazy / Cached.
   *
   * @return  unittest.web.Field[]
   */
  public function getFields() {
    if (null === $this->fields) {
      $this->fields= $this->test->getXPath()->query('.//input|.//textarea|.//select', $this->node);
    }

    $fields= [];
    foreach ($this->fields as $element) {
      $fields[]= Fields::forTag($element->tagName)->newInstance($this, $element);
    }
    return $fields;
  }

  /**
   * Get field by a specific name
   *
   * @param   string name
   * @return  unittest.web.Field
   * @throws  lang.IllegalArgumentException if the given field does not exist
   */
  public function getField($name) {
    foreach ($this->getFields() as $field) {
      if ($name === $field->getName()) return $field;
    }
    throw new \lang\IllegalArgumentException('No such field "'.$name.'"');
  }
  
  /**
   * Creates a string representation
   *
   * @return  string
   */
  public function toString() {
    return sprintf(
      '%s(action= %s, method= %s)@%s',
      nameof($this),
      $this->getAction(),
      $this->getMethod(),
      \xp::stringOf($this->getFields())
    );
  }

  /**
   * Submit the form
   *
   */
  public function submit() {
    $params= '';
    foreach ($this->getFields() as $field) {
      $params.= '&'.$field->getName().'='.urlencode($field->getValue());
    }
    $this->test->navigateTo($this->getAction(), substr($params, 1), $this->getMethod());
  }
}
