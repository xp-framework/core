<?php namespace net\xp_framework\unittest\util\cmd;
 
use unittest\TestCase;
use util\cmd\ParamString;
use lang\IllegalArgumentException;

/** @deprecated  See https://github.com/xp-framework/rfc/issues/307 */
class ParamStringTest extends TestCase {
  
  #[@test]
  public function shortFlag() {
    $p= new ParamString(['-k']);

    $this->assertTrue($p->exists('k'));
    $this->assertNull($p->value('k'));
  }

  #[@test]
  public function shortValue() {
    $p= new ParamString(['-d', 'sql']);

    $this->assertTrue($p->exists('d'));
    $this->assertEquals('sql', $p->value('d'));
  }

  #[@test]
  public function longFlag() {
    $p= new ParamString(['--verbose']);

    $this->assertTrue($p->exists('verbose'));
    $this->assertNull($p->value('verbose'));
  }

  #[@test]
  public function longValue() {
    $p= new ParamString(['--level=3']);

    $this->assertTrue($p->exists('level'));
    $this->assertEquals('3', $p->value('level'));
  }

  #[@test]
  public function longValueShortGivenDefault() {
    $p= new ParamString(['-l', '3']);

    $this->assertTrue($p->exists('level'));
    $this->assertEquals('3', $p->value('level'));
  }

  #[@test]
  public function longValueShortGiven() {
    $p= new ParamString(['-L', '3', '-l', 'FAIL']);

    $this->assertTrue($p->exists('level', 'L'));
    $this->assertEquals('3', $p->value('level', 'L'));
  }

  #[@test]
  public function positional() {
    $p= new ParamString(['That is a realm']);
    
    $this->assertTrue($p->exists(0));
    $this->assertEquals('That is a realm', $p->value(0));
  }

  #[@test]
  public function existance() {
    $p= new ParamString(['a', 'b']);
    
    $this->assertTrue($p->exists(0));
    $this->assertTrue($p->exists(1));
    $this->assertFalse($p->exists(2));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function nonExistantPositional() {
    (new ParamString(['a']))->value(1);
  }

  #[@test]
  public function nonExistantPositionalWithDefault() {
    $this->assertEquals(
      'Default', 
      (new ParamString(['--verbose']))->value(1, null, 'Default')
    );
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function nonExistantNamed() {
    (new ParamString(['--verbose']))->value('name');
  }

  #[@test]
  public function nonExistantNamedWithDefault() {
    $this->assertEquals(
      'Default', 
      (new ParamString(['--verbose']))->value('name', 'n', 'Default')
    );
  }
  
  #[@test]
  public function whitespaceInParameter() {
    $p= new ParamString(['--realm=That is a realm']);
    
    $this->assertTrue($p->exists('realm'));
    $this->assertEquals('That is a realm', $p->value('realm'));
  }
}
