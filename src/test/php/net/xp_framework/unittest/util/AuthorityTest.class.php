<?php namespace net\xp_framework\unittest\util;

use util\{Authority, Secret};
use lang\FormatException;

class AuthorityTest extends \unittest\TestCase {

  /** @return iterable */
  private function hosts() {
    yield 'example.com';
    yield '127.0.0.1';
    yield '[::1]';
  }

  #[@test]
  public function can_create() {
    new Authority('example.com');
  }

  #[@test]
  public function host() {
    $this->assertEquals('example.com', (new Authority('example.com'))->host());
  }

  #[@test]
  public function port() {
    $this->assertEquals(80, (new Authority('example.com', 80))->port());
  }

  #[@test]
  public function port_defaults_to_null() {
    $this->assertNull((new Authority('example.com'))->port());
  }

  #[@test]
  public function user() {
    $this->assertEquals('test', (new Authority('example.com', 80, 'test'))->user());
  }

  #[@test]
  public function user_defaults_to_null() {
    $this->assertNull((new Authority('example.com'))->user());
  }

  #[@test, @values([['secret'], [new Secret('secret')]])]
  public function password($password) {
    $this->assertEquals('secret', (new Authority('example.com', 80, 'test', $password))->password()->reveal());
  }

  #[@test]
  public function password_defaults_to_null() {
    $this->assertNull((new Authority('example.com'))->password());
  }

  #[@test, @values('hosts')]
  public function parse_host_only($host) {
    $this->assertEquals(new Authority($host), Authority::parse($host));
  }

  #[@test, @values('hosts')]
  public function parse_host_and_port($host) {
    $this->assertEquals(new Authority($host, 443), Authority::parse($host.':443'));
  }

  #[@test, @values('hosts')]
  public function parse_host_and_user($host) {
    $this->assertEquals(new Authority($host, null, 'test'), Authority::parse('test@'.$host));
  }

  #[@test, @values('hosts')]
  public function parse_host_and_credentials($host) {
    $this->assertEquals(new Authority($host, null, 'test', 'secret'), Authority::parse('test:secret@'.$host));
  }

  #[@test, @values('hosts')]
  public function parse_urlencoded_credentials($host) {
    $this->assertEquals(new Authority($host, null, '@test:', 'sec ret'), Authority::parse('%40test%3A:sec%20ret@'.$host));
  }

  #[@test, @expect(FormatException::class)]
  public function parse_empty() {
    Authority::parse('');
  }

  #[@test, @expect(FormatException::class), @values([
  #  'user:',
  #  'user@',
  #  'user:password@',
  #  'user@:8080',
  #  'user:password@:8080',
  #  ':8080',
  #  ':foo',
  #  ':123foo',
  #  ':foo123',
  #  'example.com:foo',
  #  'example.com:123foo',
  #  'example.com:foo123',
  #  'example$'
  #])]
  public function parse_malformed($arg) {
    Authority::parse($arg);
  }
}