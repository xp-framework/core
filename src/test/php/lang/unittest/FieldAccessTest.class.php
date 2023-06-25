<?php namespace lang\unittest;

use lang\{IllegalAccessException, IllegalArgumentException};
use unittest\actions\RuntimeVersion;
use unittest\{Assert, Action, Expect, Test, Values};

class FieldAccessTest extends FieldsTest {

  #[Test]
  public function read() {
    $fixture= $this->type('{ public $fixture= "Test"; }');
    Assert::equals('Test', $fixture->getField('fixture')->get($fixture->newInstance()));
  }

  #[Test]
  public function read_static() {
    $fixture= $this->type('{ public static $fixture= "Test"; }');
    Assert::equals('Test', $fixture->getField('fixture')->get(null));
  }

  #[Test]
  public function write() {
    $fixture= $this->type('{ public $fixture= "Test"; }');
    $instance= $fixture->newInstance();
    $fixture->getField('fixture')->set($instance, 'Changed');
    Assert::equals('Changed', $fixture->getField('fixture')->get($instance));
  }

  #[Test]
  public function write_static() {
    $fixture= $this->type('{ public static $fixture= "Test"; }');
    $fixture->getField('fixture')->set(null, 'Changed');
    Assert::equals('Changed', $fixture->getField('fixture')->get(null));
  }

  #[Test, Expect(IllegalAccessException::class), Values([['{ private $fixture; }'], ['{ protected $fixture; }']])]
  public function cannot_read_non_public($declaration) {
    $fixture= $this->type($declaration);
    $fixture->getField('fixture')->get($fixture->newInstance());
  }

  #[Test, Expect(IllegalAccessException::class), Values([['{ private $fixture; }'], ['{ protected $fixture; }']])]
  public function cannot_write_non_public($declaration) {
    $fixture= $this->type($declaration);
    $fixture->getField('fixture')->set($fixture->newInstance(), 'Test');
  }

  #[Test, Values([['{ private $fixture= "Test"; }'], ['{ protected $fixture= "Test"; }'],])]
  public function can_read_private_or_protected_via_setAccessible($declaration) {
    $fixture= $this->type($declaration);
    Assert::equals('Test', $fixture->getField('fixture')->setAccessible(true)->get($fixture->newInstance()));
  }

  #[Test, Values([['{ private $fixture= "Test"; }'], ['{ protected $fixture= "Test"; }'],])]
  public function can_write_private_or_protected_via_setAccessible($declaration) {
    $fixture= $this->type($declaration);
    $instance= $fixture->newInstance();
    $field= $fixture->getField('fixture')->setAccessible(true);
    $field->set($instance, 'Changed');
    Assert::equals('Changed', $field->get($instance));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_read_instance_method_with_incompatible() {
    $fixture= $this->type('{ public $fixture; }');
    $fixture->getField('fixture')->get($this);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_write_instance_method_with_incompatible() {
    $fixture= $this->type('{ public $fixture; }');
    $fixture->getField('fixture')->set($this, 'Test');
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.1")')]
  public function can_modify_uninitialized_readonly_property() {
    $fixture= $this->type('{ public readonly string $fixture; }');
    $field= $fixture->getField('fixture');
    $instance= $fixture->newInstance();

    $field->set($instance, 'Modified');
    Assert::equals('Modified', $field->get($instance));
  }

  #[Test, Expect(IllegalAccessException::class), Action(eval: 'new RuntimeVersion(">=8.1")')]
  public function cannot_write_readonly_property_after_initialization() {
    $fixture= $this->type('{ public readonly int $fixture; public function __construct() { $this->fixture= 1; } }');
    $fixture->getField('fixture')->set($fixture->newInstance(), 2);
  }

  #[Test, Expect(IllegalAccessException::class), Action(eval: 'new RuntimeVersion(">=8.1")')]
  public function cannot_write_readonly_property_after_initialization_via_argument_promotiom() {
    $fixture= $this->type('{ public function __construct(public readonly int $fixture) { } }');
    $fixture->getField('fixture')->set($fixture->newInstance(1), 2);
  }
}