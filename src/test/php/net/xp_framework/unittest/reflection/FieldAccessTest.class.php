<?php namespace net\xp_framework\unittest\reflection;

use lang\IllegalAccessException;
use lang\IllegalArgumentException;

class FieldAccessTest extends FieldsTest {

  #[@test]
  public function read() {
    $fixture= $this->type('{ public $fixture= "Test"; }');
    $this->assertEquals('Test', $fixture->getField('fixture')->get($fixture->newInstance()));
  }

  #[@test]
  public function read_static() {
    $fixture= $this->type('{ public static $fixture= "Test"; }');
    $this->assertEquals('Test', $fixture->getField('fixture')->get(null));
  }

  #[@test]
  public function write() {
    $fixture= $this->type('{ public $fixture= "Test"; }');
    $instance= $fixture->newInstance();
    $fixture->getField('fixture')->set($instance, 'Changed');
    $this->assertEquals('Changed', $fixture->getField('fixture')->get($instance));
  }

  #[@test]
  public function write_static() {
    $fixture= $this->type('{ public static $fixture= "Test"; }');
    $fixture->getField('fixture')->set(null, 'Changed');
    $this->assertEquals('Changed', $fixture->getField('fixture')->get(null));
  }

  #[@test, @expect(IllegalAccessException::class), @values([
  #  ['{ private $fixture; }'],
  #  ['{ protected $fixture; }']
  #])]
  public function cannot_read_non_public($declaration) {
    $fixture= $this->type($declaration);
    $fixture->getField('fixture')->get($fixture->newInstance());
  }

  #[@test, @expect(IllegalAccessException::class), @values([
  #  ['{ private $fixture; }'],
  #  ['{ protected $fixture; }']
  #])]
  public function cannot_write_non_public($declaration) {
    $fixture= $this->type($declaration);
    $fixture->getField('fixture')->set($fixture->newInstance(), 'Test');
  }

  #[@test, @values([
  #  ['{ private $fixture= "Test"; }'],
  #  ['{ protected $fixture= "Test"; }'],
  #])]
  public function can_read_private_or_protected_via_setAccessible($declaration) {
    $fixture= $this->type($declaration);
    $this->assertEquals('Test', $fixture->getField('fixture')->setAccessible(true)->get($fixture->newInstance()));
  }

  #[@test, @values([
  #  ['{ private $fixture= "Test"; }'],
  #  ['{ protected $fixture= "Test"; }'],
  #])]
  public function can_write_private_or_protected_via_setAccessible($declaration) {
    $fixture= $this->type($declaration);
    $instance= $fixture->newInstance();
    $field= $fixture->getField('fixture')->setAccessible(true);
    $field->set($instance, 'Changed');
    $this->assertEquals('Changed', $field->get($instance));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_read_instance_method_with_incompatible() {
    $fixture= $this->type('{ public $fixture; }');
    $fixture->getField('fixture')->get($this);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_write_instance_method_with_incompatible() {
    $fixture= $this->type('{ public $fixture; }');
    $fixture->getField('fixture')->set($this, 'Test');
  }
}