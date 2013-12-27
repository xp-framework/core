<?php namespace util;

/**
 * Indicates a configuration error. Use this as cause for indicating e.g.
 * application startup problems.
 */
class ConfigurationException extends \lang\XPException {
  protected $configuration;

  /**
   * Constructor
   *
   * @param   string message
   * @param   var configuration
   * @param   lang.Throwable default NULL cause causing exception
   */
  public function __construct($message, $configuration, $cause= null) {
    $this->configuration= $configuration;
    parent::__construct($message, $cause);
  }

  /**
   * Set configuration
   *
   * @param   var configuration
   */
  public function setConfiguration($configuration) {
    $this->configuration= $configuration;
  }

  /**
   * Get configuration
   *
   * @return  var
   */
  public function getConfiguration() {
    return $this->configuration;
  }
}
