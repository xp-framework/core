<?php namespace net\xp_framework\unittest\peer;

use unittest\TestCase;
use peer\URL;

/**
 * TestCase
 *
 * @see   xp://peer.URL
 * @see   https://github.com/xp-framework/xp-framework/issues/182
 * @see   rfc://rfc1738
 * @see   http://bugs.php.net/54180
 */
class URLTest extends TestCase {

  #[@test]
  public function scheme() {
    $this->assertEquals('http', (new URL('http://localhost'))->getScheme());
  }

  #[@test]
  public function schemeWithPlus() {
    $this->assertEquals('svn+ssl', (new URL('svn+ssl://localhost'))->getScheme());
  }

  #[@test]
  public function schemeMutability() {
    $this->assertEquals(
      'ftp://localhost', 
      (new URL('http://localhost'))->setScheme('ftp')->getURL()
    );
  }

  #[@test]
  public function host() {
    $this->assertEquals('localhost', (new URL('http://localhost'))->getHost());
  }

  #[@test]
  public function uppercaseHost() {
    $this->assertEquals('TEST', (new URL('http://TEST'))->getHost());
  }

  #[@test]
  public function hostMutability() {
    $this->assertEquals(
      'http://127.0.0.1', 
      (new URL('http://localhost'))->setHost('127.0.0.1')->getURL()
    );
  }

  #[@test]
  public function path() {
    $this->assertEquals('/news/index.html', (new URL('http://localhost/news/index.html'))->getPath());
  }

  #[@test]
  public function emptyPath() {
    $this->assertEquals(null, (new URL('http://localhost'))->getPath());
  }

  #[@test]
  public function slashPath() {
    $this->assertEquals('/', (new URL('http://localhost/'))->getPath());
  }

  #[@test]
  public function pathDefault() {
    $this->assertEquals('/', (new URL('http://localhost'))->getPath('/'));
  }

  #[@test]
  public function pathMutability() {
    $this->assertEquals(
      'http://localhost/index.html', 
      (new URL('http://localhost'))->setPath('/index.html')->getURL()
    );
  }

  #[@test]
  public function user() {
    $this->assertEquals('user', (new URL('http://user@localhost'))->getUser());
  }

  #[@test]
  public function emptyUser() {
    $this->assertEquals(null, (new URL('http://localhost'))->getUser());
  }

  #[@test]
  public function userDefault() {
    $this->assertEquals('nobody', (new URL('http://localhost'))->getUser('nobody'));
  }

  #[@test]
  public function urlEncodedUser() {
    $this->assertEquals('user?', (new URL('http://user%3F@localhost'))->getUser());
  }

  #[@test]
  public function setUrlEncodedUser() {
    $this->assertEquals('http://user%3F@localhost', (new URL('http://localhost'))->setUser('user?')->getURL());
  }

  #[@test]
  public function userMutability() {
    $this->assertEquals(
      'http://thekid@localhost', 
      (new URL('http://localhost'))->setUser('thekid')->getURL()
    );
  }

  #[@test]
  public function password() {
    $this->assertEquals('password', (new URL('http://user:password@localhost'))->getPassword());
  }

  #[@test]
  public function urlEncodedPassword() {
    $this->assertEquals('pass?word', (new URL('http://user:pass%3Fword@localhost'))->getPassword());
  }

  #[@test]
  public function setUrlEncodedPassword() {
    $this->assertEquals('http://user:pass%3Fword@localhost', (new URL('http://user@localhost'))->setPassword('pass?word')->getURL());
  }

  #[@test]
  public function emptyPassword() {
    $this->assertEquals(null, (new URL('http://localhost'))->getPassword());
  }

  #[@test]
  public function passwordDefault() {
    $this->assertEquals('secret', (new URL('http://user@localhost'))->getPassword('secret'));
  }

  #[@test]
  public function passwordMutability() {
    $this->assertEquals(
      'http://anon:anon@localhost', 
      (new URL('http://anon@localhost'))->setPassword('anon')->getURL()
    );
  }

  #[@test]
  public function query() {
    $this->assertEquals('a=b', (new URL('http://localhost?a=b'))->getQuery());
  }

  #[@test]
  public function queryModifiedByParams() {
    $this->assertEquals(
      'a=b&c=d', 
      (new URL('http://localhost?a=b'))->addParam('c', 'd')->getQuery()
    );
  }

  #[@test]
  public function emptyQuery() {
    $this->assertEquals(null, (new URL('http://localhost'))->getQuery());
  }

  #[@test]
  public function parameterLessQuery() {
    $this->assertEquals('1549', (new URL('http://localhost/?1549'))->getQuery());
  }

  #[@test]
  public function addToParameterLessQuery() {
    $this->assertEquals('1549&a=b', (new URL('http://localhost/?1549'))->addParam('a', 'b')->getQuery());
  }

  #[@test]
  public function ParameterLessQueryWithAdd() {
    $this->assertEquals('1549', (new URL('http://localhost/'))->addParam('1549')->getQuery());
  }

  #[@test]
  public function ParameterLessQueryWithSet() {
    $this->assertEquals('1549', (new URL('http://localhost/'))->setParam('1549')->getQuery());
  }

  #[@test]
  public function questionMarkOnly() {
    $this->assertEquals(null, (new URL('http://localhost?'))->getQuery());
  }

  #[@test]
  public function questionMarkAndFragmentOnly() {
    $this->assertEquals(null, (new URL('http://localhost?#'))->getQuery());
  }

  #[@test]
  public function queryDefault() {
    $this->assertEquals('1,2,3', (new URL('http://localhost'))->getQuery('1,2,3'));
  }

  #[@test]
  public function queryMutability() {
    $this->assertEquals(
      'http://localhost?a=b', 
      (new URL('http://localhost'))->setQuery('a=b')->getURL()
    );
  }

  #[@test]
  public function getParameterLessQuery() {
    $this->assertEquals('', (new URL('http://localhost/?1549'))->getParam('1549'));
  }

  #[@test]
  public function hasParameterLessQuery() {
    $this->assertTrue((new URL('http://localhost/?1549'))->hasParam('1549'));
  }

  /**
   * Test getFragment() method
   *
   */
  #[@test]
  public function fragment() {
    $this->assertEquals('top', (new URL('http://localhost#top'))->getFragment());
  }

  /**
   * Test getFragment() method
   *
   */
  #[@test]
  public function fragmentWithSlash() {
    $this->assertEquals('top', (new URL('http://localhost/#top'))->getFragment());
  }

  /**
   * Test getFragment() method
   *
   */
  #[@test]
  public function fragmentWithSlashAndQuestionMark() {
    $this->assertEquals('top', (new URL('http://localhost/?#top'))->getFragment());
  }

  /**
   * Test getFragment() method
   *
   */
  #[@test]
  public function fragmentWithQuery() {
    $this->assertEquals('top', (new URL('http://localhost/?query#top'))->getFragment());
  }

  /**
   * Test getFragment() method
   *
   */
  #[@test]
  public function emptyFragment() {
    $this->assertEquals(null, (new URL('http://localhost'))->getFragment());
  }

  /**
   * Test getFragment() method
   *
   */
  #[@test]
  public function hashOnly() {
    $this->assertEquals(null, (new URL('http://localhost#'))->getFragment());
  }

  /**
   * Test getFragment() method
   *
   */
  #[@test]
  public function hashAtEnd() {
    $this->assertEquals(null, (new URL('http://localhost?#'))->getFragment());
  }

  /**
   * Test getFragment() method
   *
   */
  #[@test]
  public function hashAtEndWithQuery() {
    $this->assertEquals(null, (new URL('http://localhost?query#'))->getFragment());
  }

  /**
   * Test getFragment() method when invoked with a default value
   *
   */
  #[@test]
  public function fragmentDefault() {
    $this->assertEquals('top', (new URL('http://localhost'))->getFragment('top'));
  }

  /**
   * Test setFragment()
   *
   */
  #[@test]
  public function fragmentMutability() {
    $this->assertEquals(
      'http://localhost#list', 
      (new URL('http://localhost'))->setFragment('list')->getURL()
    );
  }

  /**
   * Test getPort() method
   *
   */
  #[@test]
  public function port() {
    $this->assertEquals(8080, (new URL('http://localhost:8080'))->getPort());
  }

  /**
   * Test getPort() method
   *
   */
  #[@test]
  public function emptyPort() {
    $this->assertEquals(null, (new URL('http://localhost'))->getPort());
  }

  /**
   * Test getPort() method when invoked with a default value
   *
   */
  #[@test]
  public function portDefault() {
    $this->assertEquals(80, (new URL('http://localhost'))->getPort(80));
  }

  /**
   * Test setPort()
   *
   */
  #[@test]
  public function portMutability() {
    $this->assertEquals(
      'http://localhost:8081', 
      (new URL('http://localhost'))->setPort(8081)->getURL()
    );
  }

  #[@test]
  public function param() {
    $this->assertEquals('b', (new URL('http://localhost?a=b'))->getParam('a'));
  }

  /**
   * Test getParam() method with an array parameter
   *
   */
  #[@test]
  public function getArrayParameter() {
    $this->assertEquals(array('b'), (new URL('http://localhost?a[]=b'))->getParam('a'));
  }

  /**
   * Test getParam() method with an array parameter
   *
   */
  #[@test]
  public function getEncodedArrayParameter() {
    $this->assertEquals(array('='), (new URL('http://localhost?a[]=%3D'))->getParam('a'));
  }

  /**
   * Test getParam() method with array parameters
   *
   */
  #[@test]
  public function getArrayParameters() {
    $this->assertEquals(array('b', 'c'), (new URL('http://localhost?a[]=b&a[]=c'))->getParam('a'));
  }

  /**
   * Test getParam() method with array parameters
   *
   */
  #[@test]
  public function getArrayParametersAsHash() {
    $this->assertEquals(
      array('name' => 'b', 'color' => 'c'), 
      (new URL('http://localhost?a[name]=b&a[color]=c'))->getParam('a')
    );
  }

  /**
   * Test getParam() method with array parameters
   *
   */
  #[@test]
  public function getArrayParametersAsHashWithEncodedNames() {
    $this->assertEquals(
      array('=name=' => 'b', '=color=' => 'c'), 
      (new URL('http://localhost?a[%3Dname%3D]=b&a[%3Dcolor%3D]=c'))->getParam('a')
    );
  }

  /**
   * Test getParams() with array parameters
   *
   */
  #[@test]
  public function arrayOffsetsInDifferentArrays() {
    $this->assertEquals(
      array('a' => array('c'), 'b' => array('d')), 
      (new URL('http://localhost/?a[]=c&b[]=d'))->getParams()
    );
  }

  /**
   * Test getParam() with array parameters
   *
   */
  #[@test]
  public function duplicateOffsetsOverwriteEachother() {
    $this->assertEquals(
      array('c'), 
      (new URL('http://localhost/?a[0]=b&a[0]=c'))->getParam('a')
    );
  }

  /**
   * Test getParam() with array parameters
   *
   */
  #[@test]
  public function duplicateNamesOverwriteEachother() {
    $this->assertEquals(
      array('name' => 'c'), 
      (new URL('http://localhost/?a[name]=b&a[name]=c'))->getParam('a')
    );
  }

  /**
   * Test getParam() with array parameters
   *
   */
  #[@test]
  public function twoDimensionalArray() {
    $this->assertEquals(
      array(array('b')), 
      (new URL('http://localhost/?a[][]=b'))->getParam('a')
    );
  }

  /**
   * Test getParam() with array parameters
   *
   */
  #[@test]
  public function threeDimensionalArray() {
    $this->assertEquals(
      array(array(array('b'))), 
      (new URL('http://localhost/?a[][][]=b'))->getParam('a')
    );
  }

  /**
   * Test getParam() with array parameters
   *
   */
  #[@test]
  public function arrayOfHash() {
    $this->assertEquals(
      array(array(array('name' => 'b'))), 
      (new URL('http://localhost/?a[][][name]=b'))->getParam('a')
    );
  }

  /**
   * Test getParam() with array parameters
   *
   */
  #[@test]
  public function hashOfArray() {
    $this->assertEquals(
      array('name' => array(array('b'))), 
      (new URL('http://localhost/?a[name][][]=b'))->getParam('a')
    );
  }

  /**
   * Test getParam() with array parameters
   *
   */
  #[@test]
  public function hashOfArrayOfHash() {
    $this->assertEquals(
      array('name' => array(array('key' => 'b'))), 
      (new URL('http://localhost/?a[name][][key]=b'))->getParam('a')
    );
  }

  /**
   * Test getParam() with array parameters
   *
   */
  #[@test]
  public function hashNotationWithoutValues() {
    $this->assertEquals(
      array('name' => '', 'color' => ''), 
      (new URL('http://localhost/?a[name]&a[color]'))->getParam('a')
    );
  }

  /**
   * Test getParam() with array parameters
   *
   */
  #[@test]
  public function arrayNotationWithoutValues() {
    $this->assertEquals(
      array('', ''), 
      (new URL('http://localhost/?a[]&a[]'))->getParam('a')
    );
  }

  /**
   * Test getParams() method with array parameters
   *
   */
  #[@test]
  public function getArrayParams() {
    $this->assertEquals(
      array('a' => array('b', 'c')), 
      (new URL('http://localhost?a[]=b&a[]=c'))->getParams()
    );
  }

  /**
   * Test getParam() with array parameters
   *
   */
  #[@test]
  public function mixedOffsetsAndKeys() {
    $this->assertEquals(
      array(0 => 'b', 'name' => 'c', 1 => 'd'), 
      (new URL('http://localhost/?a[]=b&a[name]=c&a[]=d'))->getParam('a')
    );
  }

  /**
   * Test getParams() with array parameters
   *
   */
  #[@test]
  public function nestedBraces() {
    $this->assertEquals(
      array('a' => array('nested[]' => 'b')), 
      (new URL('http://localhost/?a[nested[]]=b'))->getParams()
    );
  }
 
  /**
   * Test getParams() with array parameters
   *
   */
  #[@test]
  public function nestedBracesTwice() {
    $this->assertEquals(
      array('a' => array('nested[a]' => 'b', 'nested[b]' => 'c')), 
      (new URL('http://localhost/?a[nested[a]]=b&a[nested[b]]=c'))->getParams()
    );
  }

  /**
   * Test getParams() with array parameters
   *
   */
  #[@test]
  public function nestedBracesChained() {
    $this->assertEquals(
      array('a' => array('nested[a]' => array('c'))), 
      (new URL('http://localhost/?a[nested[a]][]=c'))->getParams()
    );
  }

  /**
   * Test getParams() with array parameters
   *
   */
  #[@test]
  public function unnamedArrayParameterDoesNotArray() {
    $this->assertEquals(
      array('[]' => 'c'), 
      (new URL('http://localhost/?[]=c'))->getParams()
    );
  }

  #[@test]
  public function nonExistantParam() {
    $this->assertEquals(null, (new URL('http://localhost?a=b'))->getParam('b'));
  }

  #[@test]
  public function emptyParam() {
    $this->assertEquals('', (new URL('http://localhost?x='))->getParam('x'));
  }

  /**
   * Test getParam() method when invoked with a default value
   *
   */
  #[@test]
  public function paramDefault() {
    $this->assertEquals('x', (new URL('http://localhost?a=b'))->getParam('c', 'x'));
  }
 
  /**
   * Test addParam()
   *
   */
  #[@test]
  public function addNewParam() {
    $this->assertEquals(
      'http://localhost?a=b', 
      (new URL('http://localhost'))->addParam('a', 'b')->getURL()
    );
  }

  /**
   * Test setParam()
   *
   */
  #[@test]
  public function setNewParam() {
    $this->assertEquals(
      'http://localhost?a=b', 
      (new URL('http://localhost'))->setParam('a', 'b')->getURL()
    );
  }

  /**
   * Test addParam()
   *
   */
  #[@test]
  public function addAdditionalParam() {
    $this->assertEquals(
      'http://localhost?a=b&c=d', 
      (new URL('http://localhost?a=b'))->addParam('c', 'd')->getURL()
    );
  }

  /**
   * Test setParam()
   *
   */
  #[@test]
  public function setAdditionalParam() {
    $this->assertEquals(
      'http://localhost?a=b&c=d', 
      (new URL('http://localhost?a=b'))->setParam('c', 'd')->getURL()
    );
  }

  /**
   * Test addParam()
   *
   */
  #[@test]
  public function addAdditionalParamChained() {
    $this->assertEquals(
      'http://localhost?a=b&c=d&e=f', 
      (new URL('http://localhost?a=b'))->addParam('c', 'd')->addParam('e', 'f')->getURL()
    );
  }

  /**
   * Test setParam()
   *
   */
  #[@test]
  public function setAdditionalParamChained() {
    $this->assertEquals(
      'http://localhost?a=b&c=d&e=f', 
      (new URL('http://localhost?a=b'))->setParam('c', 'd')->setParam('e', 'f')->getURL()
    );
  }

  /**
   * Test addParam()
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function addExistingParam() {
    (new URL('http://localhost?a=b'))->addParam('a', 'b');
  }

  /**
   * Test setParam()
   *
   */
  #[@test]
  public function setExistingParam() {
    $this->assertEquals(
      'http://localhost?a=c', 
      (new URL('http://localhost?a=b'))->setParam('a', 'c')->getURL()
    );
  }

  /**
   * Test addParams()
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function addExistingParams() {
    (new URL('http://localhost?a=b'))->addParams(array('a' => 'b'));
  }

  /**
   * Test addParams()
   *
   */
  #[@test]
  public function addExistingParamsDoesNotPartiallyModify() {
    $original= 'http://localhost?a=b';
    $u= new URL($original);
    try {
      $u->addParams(array('c' => 'd', 'a' => 'b'));
      $this->fail('Existing parameter "a" not detected', null, 'lang.IllegalArgumentException');
    } catch (\lang\IllegalArgumentException $expected) { }
    $this->assertEquals($original, $u->getURL());
  }

  /**
   * Test setParams()
   *
   */
  #[@test]
  public function setExistingParams() {
    $this->assertEquals(
      'http://localhost?a=c', 
      (new URL('http://localhost?a=b'))->setParams(array('a' => 'c'))->getURL()
    );
  }

  /**
   * Test addParams()
   *
   */
  #[@test]
  public function addNewParams() {
    $this->assertEquals(
      'http://localhost?a=b&c=d', 
      (new URL('http://localhost'))->addParams(array('a' => 'b', 'c' => 'd'))->getURL()
    );
  }

  /**
   * Test setParams()
   *
   */
  #[@test]
  public function setNewParams() {
    $this->assertEquals(
      'http://localhost?a=b&c=d', 
      (new URL('http://localhost'))->setParams(array('a' => 'b', 'c' => 'd'))->getURL()
    );
  }

  /**
   * Test addParams()
   *
   */
  #[@test]
  public function addAdditionalParams() {
    $this->assertEquals(
      'http://localhost?z=x&a=b&c=d', 
      (new URL('http://localhost?z=x'))->addParams(array('a' => 'b', 'c' => 'd'))->getURL()
    );
  }

  /**
   * Test setParams()
   *
   */
  #[@test]
  public function setAdditionalParams() {
    $this->assertEquals(
      'http://localhost?z=x&a=b&c=d', 
      (new URL('http://localhost?z=x'))->setParams(array('a' => 'b', 'c' => 'd'))->getURL()
    );
  }

  /**
   * Test addParam()
   *
   */
  #[@test]
  public function addArrayParam() {
    $u= new URL('http://localhost/');
    $u->addParam('x', array('y', 'z'));
    $this->assertEquals('http://localhost/?x[]=y&x[]=z', $u->getURL());
  }

  /**
   * Test setParam()
   *
   */
  #[@test]
  public function setArrayParam() {
    $u= new URL('http://localhost/');
    $u->setParam('x', array('y', 'z'));
    $this->assertEquals('http://localhost/?x[]=y&x[]=z', $u->getURL());
  }

  /**
   * Test getParams() method
   *
   */
  #[@test]
  public function params() {
    $this->assertEquals(array('a' => 'b', 'c' => 'd'), (new URL('http://localhost?a=b&c=d'))->getParams());
  }

  #[@test]
  public function withParams() {
    $this->assertTrue((new URL('http://localhost?a=b&c=d'))->hasParams());
  }

  #[@test]
  public function withArrayParams() {
    $this->assertTrue((new URL('http://localhost?a[]=b&a[]=d'))->hasParams());
  }

  #[@test]
  public function noParams() {
    $this->assertFalse((new URL('http://localhost'))->hasParams());
  }

  #[@test]
  public function withParam() {
    $this->assertTrue((new URL('http://localhost?a=b&c=d'))->hasParam('a'));
  }

  #[@test]
  public function withArrayParam() {
    $this->assertTrue((new URL('http://localhost?a[]=b&a[]=d'))->hasParam('a'));
  }

  #[@test]
  public function withNonExistantParam() {
    $this->assertFalse((new URL('http://localhost?a=b&c=d'))->hasParam('d'));
  }

  #[@test]
  public function noParam() {
    $this->assertFalse((new URL('http://localhost'))->hasParam('a'));
  }

  #[@test]
  public function hasDotParam() {
    $this->assertTrue((new URL('http://localhost/?a.b=c'))->hasParam('a.b'));
  }

  #[@test]
  public function getDotParam() {
    $this->assertEquals('c', (new URL('http://localhost/?a.b=c'))->getParam('a.b'));
  }

  #[@test]
  public function getDotParams() {
    $this->assertEquals(array('a.b' => 'c'), (new URL('http://localhost/?a.b=c'))->getParams());
  }

  #[@test]
  public function addDotParam() {
    $this->assertEquals('a.b=c', (new URL('http://localhost/'))->addParam('a.b', 'c')->getQuery());
  }

  #[@test]
  public function removeExistingParam() {
    $this->assertEquals(new URL('http://localhost'), (new URL('http://localhost?a=b'))->removeParam('a'));
  }

  #[@test]
  public function removeNonExistantParam() {
    $this->assertEquals(new URL('http://localhost'), (new URL('http://localhost'))->removeParam('a'));
  }

  #[@test]
  public function removeExistingArrayParam() {
    $this->assertEquals(new URL('http://localhost'), (new URL('http://localhost?a[]=b&a[]=c'))->removeParam('a'));
  }

  #[@test]
  public function sameUrlsAreEqual() {
    $this->assertEquals(new URL('http://localhost'), new URL('http://localhost'));
  }

  #[@test]
  public function differentUrlsAreNotEqual() {
    $this->assertNotEquals(new URL('http://localhost'), new URL('http://example.com'));
  }

  #[@test]
  public function hashCodesForSameUrls() {
    $this->assertEquals(
      (new URL('http://localhost'))->hashCode(), 
      (new URL('http://localhost'))->hashCode()
    );
  }

  #[@test]
  public function hashCodesForDifferentUrls() {
    $this->assertNotEquals(
      (new URL('http://localhost'))->hashCode(), 
      (new URL('ftp://localhost'))->hashCode()
    );
  }

  #[@test]
  public function hashCodeRecalculated() {
    $u= new URL('http://localhost');
    $u->addParam('a', 'b');
    
    $this->assertNotEquals(
      (new URL('http://localhost'))->hashCode(), 
      $u->hashCode()
    );
  }

  #[@test, @expect('lang.FormatException')]
  public function insideAText() {
    new URL('this is the url http://url/ and nothing else');
  }

  #[@test, @expect('lang.FormatException')]
  public function doesNotSupportMailto() {
    new URL('mailto:user@example.com');
  }

  #[@test, @expect('lang.FormatException')]
  public function whiteSpaceInSchemeNotAllowed() {
    new URL('scheme ://host');
  }

  #[@test, @expect('lang.FormatException')]
  public function minusInSchemeNotAllowed() {
    new URL('scheme-minus://host');
  }

  #[@test, @expect('lang.FormatException')]
  public function underscoreInSchemeNotAllowed() {
    new URL('scheme_underscore://host');
  }

  #[@test, @expect('lang.FormatException')]
  public function numericSchemeNotAllowed() {
    new URL('123://host');
  }

  #[@test, @expect('lang.FormatException')]
  public function plusAsFirstSignInSchemeNotAllowed() {
    new URL('+v2://host');
  }

  #[@test]
  public function numericAsPartOfSchemeAllowed() {
    $this->assertEquals('foo+v2', (new URL('foo+v2://host'))->getScheme());
  }

  #[@test]
  public function oneLetterScheme() {
    $this->assertEquals('f', (new URL('f://host'))->getScheme());
  }

  #[@test, @expect('lang.FormatException')]
  public function schemeOnlyUnparseable() {
    new URL('http:');
  }

  #[@test, @expect('lang.FormatException')]
  public function schemeAndSeparatorOnlyUnparseable() {
    new URL('http://');
  }

  #[@test, @expect('lang.FormatException')]
  public function schemeSeparatorOnlyUnparseable() {
    new URL('://');
  }

  #[@test, @expect('lang.FormatException')]
  public function colonOnlyUnparseable() {
    new URL(':');
  }

  #[@test, @expect('lang.FormatException')]
  public function slashSlashOnlyUnparseable() {
    new URL('//');
  }

  #[@test, @expect('lang.FormatException')]
  public function missingSchemeUnparseable() {
    new URL(':///path/to/file');
  }

  #[@test, @expect('lang.FormatException')]
  public function emptyUnparseable() {
    new URL('');
  }

  #[@test, @expect('lang.FormatException')]
  public function withoutSchemeUnparseable() {
    new URL('/path/to/file');
  }

  #[@test, @expect('lang.FormatException')]
  public function slashOnlyUnparseable() {
    new URL('/');
  }

  /**
   * Test malformed query string parsing
   *
   */
  #[@test, @expect('lang.FormatException')]
  public function missingClosingBracket() {
    new URL('http://example.com/?a[=c');
  }

  /**
   * Test malformed query string parsing
   *
   */
  #[@test, @expect('lang.FormatException')]
  public function missingOpeningBracket() {
    new URL('http://example.com/?a]=c');
  }

  /**
   * Test malformed query string parsing
   *
   */
  #[@test, @expect('lang.FormatException')]
  public function unbalancedOpeningBrackets() {
    new URL('http://example.com/?a[[[]]=c');
  }

  /**
   * Test malformed query string parsing
   *
   */
  #[@test, @expect('lang.FormatException')]
  public function unbalancedClosingBrackets() {
    new URL('http://example.com/?a[[]]]=c');
  }

  /**
   * Test malformed query string parsing
   *
   */
  #[@test, @expect('lang.FormatException')]
  public function missingClosingBracketAfterClosed() {
    new URL('http://example.com/?a[][=c');
  }

  /**
   * Test getParams() with array parameters
   *
   */
  #[@test, @expect('lang.FormatException')]
  public function missingClosingBracketInNested() {
    new URL('http://localhost/?a[nested[a]=c');
  }

  /**
   * Test getParams() with array parameters
   *
   */
  #[@test, @expect('lang.FormatException')]
  public function missingClosingBracketInNestedAfterClosed() {
    new URL('http://localhost/?a[][nested[a]=c');
  }

  /**
   * Test getParams() with array parameters
   *
   */
  #[@test, @expect('lang.FormatException')]
  public function missingClosingBracketInNestedBeforeClosed() {
    new URL('http://localhost/?a[nested[a][]=c');
  }

  #[@test, @expect('lang.FormatException')]
  public function singleSlash() {
    new URL('http:/blah.com');
  }

  #[@test, @expect('lang.FormatException')]
  public function portOnlyNoHost() {
    new URL('http://:80');
  }

  #[@test, @expect('lang.FormatException')]
  public function userAndPortOnlyNoHost() {
    new URL('http://user@:80');
  }

  #[@test, @expect('lang.FormatException')]
  public function atSignOnlyNoHost() {
    new URL('http://@');
  }

  #[@test, @expect('lang.FormatException')]
  public function userOnlyNoHost() {
    new URL('http://user@');
  }

  #[@test, @expect('lang.FormatException')]
  public function doubleDoubleColon() {
    new URL('http://::');
  }

  #[@test, @expect('lang.FormatException')]
  public function questionMarkOnlyNoHost() {
    new URL('http://?');
  }

  #[@test, @expect('lang.FormatException')]
  public function hashSignOnlyNoHost() {
    new URL('http://#');
  }

  #[@test, @expect('lang.FormatException')]
  public function colonAndQuestionMarkOnlyNoHost() {
    new URL('http://:?');
  }

  #[@test, @expect('lang.FormatException')]
  public function questionMarkAndColonAndOnlyNoHost() {
    new URL('http://?:');
  }

  #[@test, @expect('lang.FormatException')]
  public function nonNumericPort() {
    new URL('http://example.com:ABCDEF');
  }

  #[@test, @expect('lang.FormatException')]
  public function duplicatePort() {
    new URL('http://example.com:443:443');
  }

  #[@test, @expect('lang.FormatException')]
  public function unclosedIPV6Brackets() {
    new URL('http://[::1');
  }

  #[@test, @expect('lang.FormatException')]
  public function colonInDomainNameNotAllowed() {
    new URL('http://a:o.com/');
  }

  #[@test, @expect('lang.FormatException')]
  public function percentSignInDomainNameNotAllowed() {
    new URL('http://a%o.com/');
  }

  #[@test, @expect('lang.FormatException')]
  public function spaceInDomainNameNotAllowed() {
    new URL('http://a o.com/');
  }
  
  #[@test]
  public function parseEncodedAssociativeArray() {
    $u= new URL('http://example.com/ajax?load=getXML&data%5BprojectName%5D=project&data%5BlangCode%5D=en');
    $this->assertEquals(
      array('projectName' => 'project', 'langCode' => 'en'),
      $u->getParam('data')
    );
  }

  #[@test]
  public function parseUnencodedAssociativeArray() {
    $u= new URL('http://example.com/ajax?load=getXML&data[projectName]=project&data[langCode]=en');
    $this->assertEquals(
      array('projectName' => 'project', 'langCode' => 'en'),
      $u->getParam('data')
    );
  }

  #[@test]
  public function addParamAssociativeAray() {
    $u= new URL('http://example.com/ajax?load=getXML');
    $u->addParam('data', array('projectName' => 'project', 'langCode' => 'en'));
    $this->assertEquals(
      'load=getXML&data[projectName]=project&data[langCode]=en',
      $u->getQuery()
    );
  }

  #[@test]
  public function addParamsAssociativeAray() {
    $u= new URL('http://example.com/ajax?load=getXML');
    $u->addParams(array('data' => array('projectName' => 'project', 'langCode' => 'en')));
    $this->assertEquals(
      'load=getXML&data[projectName]=project&data[langCode]=en',
      $u->getQuery()
    );
  }

  #[@test]
  public function associativeArrayQueryCalculation() {
    $u= new URL('http://example.com/ajax?load=getXML&data%5BprojectName%5D=project&data%5BlangCode%5D=en');
    $this->assertEquals(
      'load=getXML&data[projectName]=project&data[langCode]=en',
      $u->getQuery()
    );
  }
  
  #[@test]
  public function associativeArrayTwoDimensionalQueryCalculation() {
    $u= new URL('http://example.com/ajax?load=getXML&data%5Bproject%5D%5BName%5D=project&data%5Bproject%5D%5BID%5D=1337&data%5BlangCode%5D=en');
    $this->assertEquals(
      'load=getXML&data[project][Name]=project&data[project][ID]=1337&data[langCode]=en',
      $u->getQuery()
    );
  }
  
  #[@test]
  public function associativeArrayMoreDimensionalQueryCalculation() {
    $u= new URL('http://example.com/ajax?load=getXML&data%5Bproject%5D%5BName%5D%5BValue%5D=project&data%5Bproject%5D%5BID%5D%5BValue%5D=1337&data%5BlangCode%5D=en');
    $this->assertEquals(
      'load=getXML&data[project][Name][Value]=project&data[project][ID][Value]=1337&data[langCode]=en',
      $u->getQuery()
    );
  }

  #[@test]
  public function getURLWithEmptyParameter() {
    $this->assertEquals('http://example.com/test?a=v1&b&c=v2', (new URL('http://example.com/test?a=v1&b=&c=v2'))->getURL());
  }

  #[@test]
  public function getURLWithParameterWithoutValue() {
    $this->assertEquals('http://example.com/test?a=v1&b&c=v2', (new URL('http://example.com/test?a=v1&b&c=v2'))->getURL());
  }

  #[@test]
  public function getURLAfterSettingEmptyQueryString() {
    $this->assertEquals('http://example.com/test', (new URL('http://example.com/test'))->setQuery('')->getURL());
  }

  #[@test]
  public function getURLAfterSettingNullQueryString() {
    $this->assertEquals('http://example.com/test', (new URL('http://example.com/test'))->setQuery(null)->getURL());
  }

  #[@test]
  public function getURLWithEmptyQueryStringConstructor() {
    $this->assertEquals('http://example.com/test', (new URL('http://example.com/test?'))->getURL());
  }

  #[@test]
  public function fragmentWithQuestionMark() {
    $url= new URL('http://example.com/path/script.html#fragment?data');
    $this->assertEquals('/path/script.html', $url->getPath());
    $this->assertEquals('fragment?data', $url->getFragment());
  }
 
  #[@test]
  public function ipv4Address() {
    $this->assertEquals('64.246.30.37', (new URL('http://64.246.30.37'))->getHost());
  }

  #[@test]
  public function ipv6Address() {
    $this->assertEquals('[::1]', (new URL('http://[::1]'))->getHost());
  }

  #[@test]
  public function ipv4AddressAndPort() {
    $u= new URL('http://64.246.30.37:8080');
    $this->assertEquals('64.246.30.37', $u->getHost());
    $this->assertEquals(8080, $u->getPort());
  }

  #[@test]
  public function ipv6AddressAndPort() {
    $u= new URL('http://[::1]:8080');
    $this->assertEquals('[::1]', $u->getHost());
    $this->assertEquals(8080, $u->getPort());
  }

  #[@test]
  public function fileUrl() {
    $u= new URL('file:///etc/passwd');
    $this->assertEquals(null, $u->getHost());
    $this->assertEquals('/etc/passwd', $u->getPath());
  }

  #[@test]
  public function hostInFileUrl() {
    $u= new URL('file://localhost/etc/passwd');
    $this->assertEquals('localhost', $u->getHost());
    $this->assertEquals('/etc/passwd', $u->getPath());
  }

  #[@test]
  public function windowsDriveInFileUrl() {
    $u= new URL('file:///c:/etc/passwd');
    $this->assertEquals(null, $u->getHost());
    $this->assertEquals('c:/etc/passwd', $u->getPath());
  }

  #[@test]
  public function windowsDriveInFileUrlWithHost() {
    $u= new URL('file://localhost/c:/etc/passwd');
    $this->assertEquals('localhost', $u->getHost());
    $this->assertEquals('c:/etc/passwd', $u->getPath());
  }

  #[@test]
  public function windowsDriveInFileUrlWithPipe() {
    $u= new URL('file:///c|/etc/passwd');
    $this->assertEquals(null, $u->getHost());
    $this->assertEquals('c:/etc/passwd', $u->getPath());
  }

  #[@test]
  public function windowsDriveInFileUrlWithPipeWithHost() {
    $u= new URL('file://localhost/c|/etc/passwd');
    $this->assertEquals('localhost', $u->getHost());
    $this->assertEquals('c:/etc/passwd', $u->getPath());
  }

  #[@test]
  public function sqliteUrl() {
    $u= new URL('sqlite:///path/to/file.db');
    $this->assertEquals(null, $u->getHost());
    $this->assertEquals('/path/to/file.db', $u->getPath());
  }

  #[@test]
  public function parseIpv6LocalhostURL() {
    $this->assertEquals('http://[::1]:80/authenticate/', (new URL('http://[::1]:80/authenticate/'))->getURL());
  }

  #[@test]
  public function parseIpv6URL() {
    $this->assertEquals('http://[2001:8d8f:1fe:5:abba:dbff:fefe:7755]:80/authenticate/', (new URL('http://[2001:8d8f:1fe:5:abba:dbff:fefe:7755]:80/authenticate/'))->getURL());
  }

  #[@test]
  public function canonicalURLScheme() {
   $this->assertEquals('https://localhost/', (new URL('https+v3://localhost'))->getCanonicalUrl());
  }

  #[@test]
  public function canonicalURLLowerCaseHost() {
    $this->assertEquals('http://localhost/', (new URL('http://LOCALHOST'))->getCanonicalUrl());
  }
  
  #[@test]
  public function failCanonicalURLLowerCaseHost() {
    $this->assertNotEquals('http://LOCALHOST/', (new URL('http://LOCALHOST'))->getCanonicalUrl());
  }
  
  #[@test]
  public function canonicalURLRemoveDefaultPort() {
    $this->assertEquals('http://localhost/', (new URL('http://localhost:80'))->getCanonicalUrl());
  }
  
  #[@test]
  public function canonicalURLPort() {
    $this->assertEquals('http://localhost:81/', (new URL('http://localhost:81'))->getCanonicalUrl());
  }
  
  #[@test]
  public function canonicalURLCapitalizeLettersInEscapeSequenceForPath() {
    $this->assertEquals('http://localhost/a%C2%B1b', (new URL('http://localhost/a%c2%b1b'))->getCanonicalUrl());
  }
  
  #[@test]
  public function canonicalURLdecodePercentEncodedOctetsForPath() {
    $this->assertEquals('http://localhost/-._~', (new URL('http://localhost/%2D%2E%5F%7E'))->getCanonicalUrl());
  }
  
  #[@test]
  public function canonicalURLremoveDotSegmentsForPath() {
    $this->assertEquals('http://localhost/a/g', (new URL('http://localhost/a/b/c/./../../g'))->getCanonicalUrl());
  }
  
  #[@test]
  public function canonicalURL() {
    $srcURL='https+v3://LOCALHOST:443/%c2/%7E?q1=%2D&q2=%b1#/a/b/c/./../../g';
    $destURL='https://localhost/%C2/~?q1=-&q2=%B1#/a/g';
    $this->assertEquals($destURL, (new URL($srcURL))->getCanonicalUrl());
  }

  #[@test]
  public function atInParams() {
    $this->assertEquals('@', (new URL('http://localhost/?q=@'))->getParam('q'));
  }

  #[@test]
  public function atInQuerystring() {
    $this->assertEquals('%40', (new URL('http://localhost/?@'))->getQuery());
  }

  #[@test]
  public function atInFragment() {
    $this->assertEquals('@', (new URL('http://localhost/#@'))->getFragment());
  }

  #[@test]
  public function atInPath() {
    $this->assertEquals('/@', (new URL('http://localhost/@'))->getPath());
  }

  #[@test]
  public function atInUserAndPath() {
    $u= new URL('http://user@localhost/@');
    $this->assertEquals('user', $u->getUser());
    $this->assertEquals('/@', $u->getPath());
  }

  #[@test, @values([
  #  'http://localhost/',
  #  'http://localhost:8080/',
  #  'http://localhost/path',
  #  'http://localhost/path?query',
  #  'http://localhost/path?query#fragment',
  #  'http://user@localhost/path?query#fragment'
  #])]
  public function string_representation($input) {
    $this->assertEquals($input, (new URL($input))->toString());
  }

  #[@test]
  public function string_representation_does_not_include_password() {
    $u= new URL('http://user:pass@localhost/path?query#fragment');
    $this->assertEquals('http://user:********@localhost/path?query#fragment', $u->toString());
  }
}
