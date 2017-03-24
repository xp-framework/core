<?php namespace net\xp_framework\unittest\util;

use util\{URI, Authority};
use lang\{Primitive, FormatException};

class URITest extends \unittest\TestCase {

  /** @return iterable */
  private function opaqueUris() {
    yield [new URI('mailto:fred@example.com')];
    yield [new URI('news:comp.infosystems.www.servers.unix')];
    yield [new URI('tel:+1-816-555-1212')];
    yield [new URI('urn:isbn:096139210x')];
  }

  /** @return iterable */
  private function hierarchicalUris() {
    yield [new URI('http://example.com')];
    yield [new URI('http://127.0.0.1:8080')];
    yield [new URI('http://user:pass@[::1]')];
    yield [new URI('ldap://example.com/c=GB?objectClass?one')];
  }

  /** @return iterable */
  private function relativeUris() {
    yield [new URI('/index.html')];
    yield [new URI('../../demo/index.html')];
    yield [new URI('//example.com/?a=b')];
  }

  #[@test, @values('opaqueUris')]
  public function opaque_uris($uri) {
    $this->assertEquals([true, false], [$uri->isOpaque(), $uri->isRelative()]);
  }

  #[@test, @values('hierarchicalUris')]
  public function hierarchical_uris($uri) {
    $this->assertEquals([false, false], [$uri->isOpaque(), $uri->isRelative()]);
  }

  #[@test, @values('relativeUris')]
  public function relative_uris($uri) {
    $this->assertTrue($uri->isRelative());
  }

  #[@test, @values('opaqueUris')]
  public function opaque_uris_have_no_authority($uri) {
    $this->assertNull($uri->authority());
  }

  #[@test, @values('hierarchicalUris')]
  public function hierarchical_uris_have_authority($uri) {
    $this->assertInstanceOf(Authority::class, $uri->authority());
  }

  #[@test]
  public function scheme() {
    $this->assertEquals('http', (new URI('http://example.com'))->scheme());
  }

  #[@test]
  public function authority() {
    $this->assertEquals(new Authority('example.com', 8080), (new URI('http://example.com:8080'))->authority());
  }

  #[@test, @values([
  #  'http://example.com',
  #  'http://example.com:8080',
  #  'http://user:pass@example.com',
  #  'ldap://example.com/c=GB?objectClass?one'
  #])]
  public function domain_as_host($uri) {
    $this->assertEquals('example.com', (new URI($uri))->host());
  }

  #[@test, @values([
  #  'http://127.0.0.1',
  #  'http://127.0.0.1:8080',
  #  'http://user:pass@127.0.0.1',
  #  'ldap://127.0.0.1/c=GB?objectClass?one'
  #])]
  public function ipv4_address_as_host($uri) {
    $this->assertEquals('127.0.0.1', (new URI($uri))->host());
  }

  #[@test, @values([
  #  'http://[::1]',
  #  'http://[::1]:8080',
  #  'http://user:pass@[::1]',
  #  'ldap://[::1]/c=GB?objectClass?one'
  #])]
  public function ipv6_address_as_host($uri) {
    $this->assertEquals('[::1]', (new URI($uri))->host());
  }

  #[@test]
  public function without_port() {
    $this->assertEquals(null, (new URI('http://example.com'))->port());
  }

  #[@test]
  public function with_port() {
    $this->assertEquals(8080, (new URI('http://example.com:8080'))->port());
  }

  #[@test]
  public function without_path() {
    $this->assertEquals(null, (new URI('http://example.com'))->path());
  }

  #[@test]
  public function with_empty_path() {
    $this->assertEquals('/', (new URI('http://example.com/'))->path());
  }

  #[@test]
  public function with_path() {
    $this->assertEquals('/a/b/c', (new URI('http://example.com/a/b/c'))->path());
  }

  #[@test]
  public function file_url_path() {
    $this->assertEquals('/usr/local/etc/php.ini', (new URI('file:///usr/local/etc/php.ini'))->path());
  }

  #[@test, @values([
  #  'http://api@example.com',
  #  'http://api:secret@example.com',
  #  'http://api:secret@example.com:8080',
  #])]
  public function with_user($uri) {
    $this->assertEquals('api', (new URI($uri))->user());
  }

  #[@test]
  public function without_user() {
    $this->assertEquals(null, (new URI('http://example.com'))->user());
  }

  #[@test]
  public function urlencoded_user() {
    $this->assertEquals('u:root', (new URI('http://u%3Aroot@example.com'))->user());
  }

  #[@test, @values([
  #  'http://api:secret@example.com',
  #  'http://api:secret@example.com:8080'
  #])]
  public function with_password($uri) {
    $this->assertEquals('secret', (new URI($uri))->password()->reveal());
  }

  #[@test]
  public function without_password() {
    $this->assertEquals(null, (new URI('http://example.com'))->password());
  }

  #[@test]
  public function urlencoded_password() {
    $this->assertEquals('p:secret', (new URI('http://u%3Aroot:p%3Asecret@example.com'))->password()->reveal());
  }

  #[@test]
  public function without_query() {
    $this->assertEquals(null, (new URI('http://example.com'))->query());
  }

  #[@test, @values([
  #  'http://example.com?a=b&c=d',
  #  'http://example.com/?a=b&c=d',
  #  'http://example.com/path?a=b&c=d',
  #  'http://example.com/?a=b&c=d#',
  #  'http://example.com/?a=b&c=d#fragment'
  #])]
  public function with_query($uri) {
    $this->assertEquals('a=b&c=d', (new URI($uri))->query());
  }

  #[@test]
  public function query_for_opaque_uri() {
    $this->assertEquals('Subject=Hello%20World', (new URI('mailto:fred@example.com?Subject=Hello%20World'))->query());
  }

  #[@test]
  public function query_with_question_mark() {
    $this->assertEquals('objectClass?one', (new URI('ldap://[2001:db8::7]/c=GB?objectClass?one'))->query());
  }

  #[@test]
  public function without_fragment() {
    $this->assertEquals(null, (new URI('http://example.com'))->fragment());
  }

  #[@test, @values([
  #  'http://example.com#top',
  #  'http://example.com/#top',
  #  'http://example.com/path#top',
  #  'http://example.com/a=b&c=d#top'
  #])]
  public function with_fragment($uri) {
    $this->assertEquals('top', (new URI($uri))->fragment());
  }

  #[@test, @values([
  #  'http://example.com',
  #  'http://example.com:80',
  #  'http://user@example.com',
  #  'http://user:pass@example.com',
  #  'http://u%3Aroot:p%3Asecret@example.com',
  #  'http://example.com?param=value',
  #  'http://example.com/#fragment',
  #  'http://example.com//path',
  #  'http://example.com/path/with%2Fslashes',
  #  'http://example.com/path?param=value&ie=utf8#fragment',
  #  'https://example.com',
  #  'https://[::1]:443',
  #  'ftp://example.com',
  #  'file:///usr/local/etc/php.ini',
  #  'file:///c:/php/php.ini',
  #  'file:///c|/php/php.ini',
  #  'tel:+1-816-555-1212',
  #  'urn:oasis:names:specification:docbook:dtd:xml:4.1.2',
  #  'ldap://[2001:db8::7]/c=GB?objectClass?one',
  #  'index.html'
  #])]
  public function string_cast_yields_input($input) {
    $this->assertEquals($input, (string)new URI($input));
  }

  #[@test]
  public function string_representation_does_not_include_password() {
    $this->assertEquals(
      'util.URI<http://user:********@example.com>',
      (new URI('http://user:pass@example.com'))->toString()
    );
  }

  #[@test]
  public function can_be_created_via_with() {
    $this->assertEquals(
      'https://example.com:443/',
      (string)URI::with()->scheme('https')->authority(new Authority('example.com', 443))->path('/')->create()
    );
  }

  #[@test]
  public function can_be_modified_via_using() {
    $this->assertEquals(
      'http://example.com?a=b',
      (string)(new URI('http://example.com'))->using()->query('a=b')->create()
    );
  }

  #[@test]
  public function compared_to_itself() {
    $uri= new URI('https://example.com/');
    $this->assertEquals(0, $uri->compareTo($uri));
  }

  #[@test, @values([
  #  ['https://example.com', 1],
  #  ['https://example.com/', 0],
  #  ['https://example.com/path', -1]
  #])]
  public function compared_to_another($uri, $expected) {
    $this->assertEquals($expected, (new URI('https://example.com/'))->compareTo(new URI($uri)));
  }

  #[@test, @values([
  #  [null],
  #  [false], [true],
  #  [0], [6100], [-1],
  #  [0.0], [1.5],
  #  [''], ['Hello'], ['https://example.com/'],
  #  [[]], [[1, 2, 3]],
  #  [['key' => 'value']],
  #  [Primitive::$STRING]
  #])]
  public function compared_to($value) {
    $this->assertEquals(1, (new URI('https://example.com/'))->compareTo($value));
  }

  #[@test]
  public function canonicalize() {
    $this->assertEquals(new URI('http://localhost/'), (new URI('http://localhost:80'))->canonicalize());
  }

  #[@test, @expect(FormatException::class)]
  public function empty_input() {
    new URI('');
  }

  #[@test, @expect(class= FormatException::class, withMessage= '/Authority malformed/'), @values([
  #  'http://user:',
  #  'http://user@',
  #  'http://user:password@',
  #  'http://user@:8080',
  #  'http://:8080',
  #  'http://:foo',
  #  'http://:123foo',
  #  'http://:foo123',
  #  'http://example.com:foo',
  #  'http://example.com:123foo',
  #  'http://example.com:foo123',
  #  'http://example$'
  #])]
  public function malformed_authority($arg) {
    new URI($arg);
  }

  #[@test]
  public function semantic_attack() {

    // See https://tools.ietf.org/html/rfc3986#section-7.6
    $this->assertEquals(
      'cnn.example.com&story=breaking_news',
      (new URI('ftp://cnn.example.com&story=breaking_news@10.0.0.1/top_story.htm'))->authority()->user()
    );
  }

  #[@test, @values([
  #  [new URI('http://localhost', 'index.html')],
  #  [new URI('http://localhost/', 'index.html')],
  #  [new URI('http://localhost/.', 'index.html')],
  #  [new URI('http://localhost/./', 'index.html')],
  #  [new URI('http://localhost/home', '../index.html')],
  #  [new URI('http://localhost/home/', '../index.html')]
  #])]
  public function with_relative_part($uri) {
    $this->assertEquals(new URI('http://localhost/index.html'), $uri->canonicalize());
  }

  #[@test, @values([
  #  [new URI('http://localhost', '?a=b')],
  #  [new URI('http://localhost/', '?a=b')],
  #  [new URI('http://localhost/.', '?a=b')]
  #])]
  public function with_relative_part_including_query($uri) {
    $this->assertEquals(new URI('http://localhost/?a=b'), $uri->canonicalize());
  }

  #[@test, @values([
  #  [new URI('http://localhost', '#top')],
  #  [new URI('http://localhost/', '#top')],
  #  [new URI('http://localhost/.', '#top')]
  #])]
  public function with_relative_part_including_fragment($uri) {
    $this->assertEquals(new URI('http://localhost/#top'), $uri->canonicalize());
  }

  #[@test, @values([
  #  [new URI('http://localhost'), 'index.html', new URI('http://localhost/index.html')],
  #  [new URI('http://localhost'), '?a=b', new URI('http://localhost?a=b')],
  #  [new URI('http://localhost'), '#top', new URI('http://localhost#top')],
  #  [new URI('http://localhost'), 'index.html?a=b', new URI('http://localhost/index.html?a=b')],
  #  [new URI('http://localhost'), 'index.html#top', new URI('http://localhost/index.html#top')],
  #  [new URI('http://localhost/home.html'), 'index.html', new URI('http://localhost/index.html')],
  #  [new URI('http://localhost?c=d'), 'index.html?a=b', new URI('http://localhost/index.html?a=b')],
  #  [new URI('http://localhost?c=d'), 'index.html#top', new URI('http://localhost/index.html#top')],
  #  [new URI('http://localhost#top'), 'index.html', new URI('http://localhost/index.html')],
  #  [new URI('http://localhost/home/'), 'index.html', new URI('http://localhost/home/index.html')],
  #  [new URI('http://localhost/home'), '/index.html', new URI('http://localhost/index.html')],
  #  [new URI('http://localhost'), '//example.com', new URI('http://example.com')],
  #  [new URI('http://localhost/home'), '//example.com', new URI('http://example.com')],
  #  [new URI('http://localhost'), 'https://example.com', new URI('https://example.com')],
  #  [new URI('http://localhost/home'), 'https://example.com', new URI('https://example.com')]
  #])]
  public function resolve($uri, $resolve, $result) {
    $this->assertEquals($result, $uri->resolve($resolve));
  }
}