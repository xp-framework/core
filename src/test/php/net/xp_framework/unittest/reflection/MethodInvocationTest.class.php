<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\TargetInvocationException;
use lang\{IllegalAccessException, IllegalArgumentException};
use unittest\actions\RuntimeVersion;
use unittest\{Action, Expect, Test, Values};

class MethodInvocationTest extends MethodsTest {

  #[Test]
  public function invoke_instance() {
    $fixture= $this->type('{ public function fixture() { return "Test"; } }');
    $this->assertEquals('Test', $fixture->getMethod('fixture')->invoke($fixture->newInstance(), []));
  }

  #[Test]
  public function invoke_static() {
    $fixture= $this->type('{ public static function fixture() { return "Test"; } }');
    $this->assertEquals('Test', $fixture->getMethod('fixture')->invoke(null, []));
  }

  #[Test]
  public function invoke_passes_arguments() {
    $fixture= $this->type('{ public function fixture($a, $b) { return $a + $b; } }');
    $this->assertEquals(3, $fixture->getMethod('fixture')->invoke($fixture->newInstance(), [1, 2]));
  }

  #[Test]
  public function invoke_method_without_return() {
    $fixture= $this->type('{ public function fixture() { } }');
    $this->assertNull($fixture->getMethod('fixture')->invoke($fixture->newInstance(), []));
  }

  #[Test, Expect(TargetInvocationException::class)]
  public function cannot_invoke_instance_method_without_object() {
    $fixture= $this->type('{ public function fixture() { } }');
    $fixture->getMethod('fixture')->invoke(null, []);
  }

  #[Test, Expect(TargetInvocationException::class)]
  public function exceptions_raised_during_invocation_are_wrapped() {
    $fixture= $this->type('{ public function fixture() { throw new \lang\IllegalAccessException("Test"); } }');
    $fixture->getMethod('fixture')->invoke($fixture->newInstance(), []);
  }

  #[Test, Expect(TargetInvocationException::class), Action(new RuntimeVersion('>=7.0'))]
  public function exceptions_raised_for_return_type_violations() {
    $fixture= $this->type('{ public function fixture(): array { return null; } }');
    $fixture->getMethod('fixture')->invoke($fixture->newInstance(), []);
  }

  #[Test, Expect(TargetInvocationException::class), Action(new RuntimeVersion('>=7.0'))]
  public function exceptions_raised_for_parameter_type_violations() {
    $fixture= $this->type('{ public function fixture(int $i) { } }');
    $fixture->getMethod('fixture')->invoke($fixture->newInstance(), ['abc']);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_invoke_instance_method_with_incompatible() {
    $fixture= $this->type('{ public function fixture() { } }');
    $fixture->getMethod('fixture')->invoke($this, []);
  }

  #[Test, Expect(IllegalAccessException::class), Values([['{ private function fixture() { } }'], ['{ protected function fixture() { } }'], ['{ public abstract function fixture(); }', 'abstract'],])]
  public function cannot_invoke_non_public($declaration, $modifiers= '') {
    $fixture= $this->type($declaration, ['modifiers' => $modifiers]);
    $fixture->getMethod('fixture')->invoke($fixture->newInstance(), []);
  }

  #[Test, Values([['{ private function fixture() { return "Test"; } }'], ['{ protected function fixture() { return "Test"; } }'],])]
  public function can_invoke_private_or_protected_via_setAccessible($declaration) {
    $fixture= $this->type($declaration);
    $this->assertEquals('Test', $fixture->getMethod('fixture')->setAccessible(true)->invoke($fixture->newInstance(), []));
  }
}