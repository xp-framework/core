<?php

  /* This class is part of the XP framework
   *
   * $Id$
   */

  uses(
    'unittest.TestCase',
    'scriptlet.xml.XMLScriptletURL'
  );

  /**
   * TestCase
   *
   * @see       ...
   * @purpose   TestCase for
   */
  class XMLScriptletURLTest extends TestCase {

    /**
     * Test
     *
     */
    #[@test]
    public function emptyPath() {
      $url= new XMLScriptletURL('http://xp-framework.net/');
      $this->assertEquals('http://xp-framework.net/xml/', $url->getURL());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function xmlUrlHasProduct() {
      $url= new XMLScriptletURL('http://xp-framework.net/xml/xp.de_DE/home');
      $this->assertEquals('xp', $url->getProduct());
    }
    /**
     * Test
     *
     */
    #[@test]
    public function xmlUrlHasLanguage() {
      $url= new XMLScriptletURL('http://xp-framework.net/xml/xp.de_DE/home');
      $this->assertEquals('de_DE', $url->getLanguage());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function xmlUrlHasState() {
      $url= new XMLScriptletURL('http://xp-framework.net/xml/xp.de_DE/home');
      $this->assertEquals('home', $url->getStateName());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function urlEitherHasProductOrLanguageOrNothingAtAll() {
      $url= new XMLScriptletURL('http://xp-framework.net/xml/xp/home');
      $this->assertEquals(NULL, $url->getProduct());
      $this->assertEquals(NULL, $url->getLanguage());
      $this->assertEquals('xp/home', $url->getStateName());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function urlHasPsessionId() {
      $url= new XMLScriptletURL('http://xp-framework.net/xml/psessionid=12345/home');
      $this->assertEquals('12345', $url->getSessionId());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function urlHasPage() {
      $url= new XMLScriptletURL('http://xp-framework.net/xml/psessionid=12345/home?__page=print');
      $this->assertEquals('print', $url->getPage());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function getURL() {
      $url= new XMLScriptletURL('http://xp-framework.net/xml/xp.de_DE.psessionid=foo123456bar/home?key=value');

      $this->assertEquals(
        'http://xp-framework.net/xml/xp.de_DE.psessionid=foo123456bar/home?key=value',
        $url->getURL()
      );
    }

    /**
     * Test
     *
     */
    #[@test]
    public function getUrlContainsPort() {
      $url= new XMLScriptletURL('http://xp-framework.net:8080/xml/home');
      $this->assertEquals('http://xp-framework.net:8080/xml/home', $url->getURL());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function getUrlDoesNotContainDefaultPort() {
      $url= new XMLScriptletURL('http://xp-framework.net:80/xml/home');
      $this->assertEquals('http://xp-framework.net/xml/home', $url->getURL());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function getUrlDoesNotContainDefaultPortForHttps() {
      $url= new XMLScriptletURL('https://xp-framework.net:443/xml/home');
      $this->assertEquals('https://xp-framework.net/xml/home', $url->getURL());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function getURLStripsDefaultValuesButNotState() {
      $url= new XMLScriptletURL('http://xp-framework.net/xml/a.en_US/home');

      $url->setDefaultProduct('a');
      $url->setDefaultLanguage('en_US');
      $url->setDefaultStateName('home');

      $this->assertEquals('http://xp-framework.net/xml/home', $url->getURL());
    }
  }
?>
