<?php namespace net\xp_framework\unittest\util;

use util\{URI, URICanonicalization};

class URICanonicalizationTest extends \unittest\TestCase {

  /**
   * Assertion helper
   *
   * @param  string $expected
   * @param  string $input
   * @throws unittest.AssertionFailedError
   */
  private function assertCanonical($expected, $input) {
    $this->assertEquals(new URI($expected), (new URICanonicalization())->canonicalize(new URI($input)));
  }

  #[@test]
  public function scheme_is_lowercased() {
    $this->assertCanonical('http://localhost/', 'HTTP://localhost/');
  }

  #[@test]
  public function works_without_authority() {
    $this->assertCanonical('mailto:test@example.com', 'mailto:test@example.com');
  }

  #[@test]
  public function scheme_argument_is_removed() {
    $this->assertCanonical('https://localhost/', 'https+v3://localhost/');
  }

  #[@test]
  public function host_is_lowercased() {
    $this->assertCanonical('http://localhost/', 'http://LOCALHOST/');
  }

  #[@test]
  public function path_defaults_to_root() {
    $this->assertCanonical('http://localhost/', 'http://localhost');
  }

  #[@test, @values([
  #  ['http://example.com:80/', 'http://example.com/'],
  #  ['https://example.com:443/', 'https://example.com/'],
  #  ['ftp://example.com:22/', 'ftp://example.com:22/'],
  #  ['http://example.com:8080/', 'http://example.com:8080/'],
  #  ['https://example.com:8443/', 'https://example.com:8443/']
  #])]
  public function http_and_https_default_ports_are_removed($input, $expected) {
    $this->assertCanonical($expected, $input);
  }

  #[@test]
  public function escape_sequence_handling_in_path() {
    $this->assertCanonical('http://localhost/a%C2%B1b-._~%FC', 'http://localhost/a%c2%b1b%2D%2E%5F%7E%FC');
  }

  #[@test]
  public function escape_sequence_handling_in_query() {
    $this->assertCanonical('http://localhost/?param=a%C2%B1b-._~%FC', 'http://localhost/?param=a%c2%b1b%2D%2E%5F%7E%FC');
  }

  #[@test]
  public function escape_sequence_handling_in_fragment() {
    $this->assertCanonical('http://localhost/#a%C2%B1b-._~%FC', 'http://localhost/#a%c2%b1b%2D%2E%5F%7E%FC');
  }

  #[@test, @values([
  #  ['http://localhost/.', 'http://localhost/'],
  #  ['http://localhost/..', 'http://localhost/'],
  #  ['http://localhost/a/./b', 'http://localhost/a/b'],
  #  ['http://localhost/a/././b', 'http://localhost/a/b'],
  #  ['http://localhost/a/../b', 'http://localhost/b'],
  #  ['http://localhost/a/./.././b', 'http://localhost/b'],
  #  ['http://localhost/a/b/c/./../../d', 'http://localhost/a/d'],
  #])]
  public function removing_dot_sequences_from_path($input, $expected) {
    $this->assertCanonical($expected, $input);
  }
}