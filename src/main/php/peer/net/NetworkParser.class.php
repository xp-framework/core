<?php namespace peer\net;

/**
 * Description of NetworkParser
 *
 * @test  xp://net.xp_framework.unittest.peer.net.NetworkParserTest
 */
class NetworkParser extends \lang\Object {
  protected $addressParser  = null;

  /**
   * Constructor
   *
   */
  public function __construct() {
    $this->addressParser= new InetAddressFactory();
  }

  /**
   * Parse given string into network object
   *
   * @param   string string
   * @return  peer.Network
   * @throws  lang.FormatException if string could not be parsed
   */
  public function parse($string) {
    if (2 !== sscanf($string, '%[^/]/%d$', $addr, $mask)) 
      throw new \lang\FormatException('Given string cannot be parsed to network: ['.$string.']');

    return new Network($this->addressParser->parse($addr), $mask);
  }

  /**
   * Parse given string into network object, return NULL if it fails.
   *
   * @param   string string
   * @return  peer.Network
   */
  public function tryParse($string) {
    try {
      return $this->parse($string);
    } catch (\lang\FormatException $e) {
      return null;
    }
  }
}
