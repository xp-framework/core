<?php namespace net\xp_framework\unittest\util;

use util\{URI, URICreation, Authority, Secret};

class URICreationTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new URICreation();
  }

  #[@test]
  public function can_create_with_uri() {
    new URICreation(new URI('http://example.com'));
  }

  #[@test]
  public function opaque_uri() {
    $this->assertEquals(
      new URI('mailto:test@example.com'),
      (new URICreation())->scheme('mailto')->path('test@example.com')->create()
    );
  }

  #[@test, @values([['test@example.com:22'], [new Authority('example.com', 22, 'test')]])]
  public function hierarchical_uri($authority) {
    $this->assertEquals(
      new URI('ssh://test@example.com:22'),
      (new URICreation())->scheme('ssh')->authority($authority)->create()
    );
  }

  #[@test]
  public function file_uri_requires_empty_authority() {
    $this->assertEquals(
      new URI('file:///usr/local/etc/php.ini'),
      (new URICreation())->scheme('file')->authority(Authority::$EMPTY)->path('/usr/local/etc/php.ini')->create()
    );
  }

  #[@test]
  public function host() {
    $this->assertEquals(
      new URI('http://example.com'),
      (new URICreation())->scheme('http')->host('example.com')->create()
    );
  }

  #[@test]
  public function port() {
    $this->assertEquals(
      new URI('http://example.com:80'),
      (new URICreation())->scheme('http')->host('example.com')->port(80)->create()
    );
  }

  #[@test]
  public function user() {
    $this->assertEquals(
      new URI('http://test@example.com'),
      (new URICreation())->scheme('http')->host('example.com')->user('test')->create()
    );
  }

  #[@test, @values([['secret'], [new Secret('secret')]])]
  public function password($password) {
    $this->assertEquals(
      new URI('http://test:secret@example.com'),
      (new URICreation())->scheme('http')->host('example.com')->user('test')->password($password)->create()
    );
  }

  #[@test]
  public function query() {
    $this->assertEquals(
      new URI('mailto:test@example.com?Subject=Hello'),
      (new URICreation())->scheme('mailto')->path('test@example.com')->query('Subject=Hello')->create()
    );
  }

  #[@test]
  public function fragment() {
    $this->assertEquals(
      new URI('http://example.com#home'),
      (new URICreation())->scheme('http')->host('example.com')->fragment('home')->create()
    );
  }

  #[@test]
  public function modify_uri_given_to_constructor() {
    $this->assertEquals(
      new URI('https://example.com:443'),
      (new URICreation(new URI('http://example.com')))->scheme('https')->port(443)->create()
    );
  }
}