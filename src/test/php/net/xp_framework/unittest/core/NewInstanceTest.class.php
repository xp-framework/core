<?php namespace net\xp_framework\unittest\core;

use lang\reflect\Package;
use lang\{Runnable, Runtime, Process, Value, ClassLoader, ClassFormatException, IllegalAccessException};
use net\xp_framework\unittest\Name;
use unittest\actions\{VerifyThat, RuntimeVersion};
use util\Objects;

/**
 * TestCase for newinstance() functionality. Some tests are skipped if
 * process execution has been disabled.
 */
class NewInstanceTest extends \unittest\TestCase {

  /** @return bool */
  protected function processExecutionEnabled() {
    return !Process::$DISABLED;
  }

  /**
   * Runs sourcecode inside a new runtime
   *
   * @param   string $src
   * @return  var[] an array with three elements: exitcode, stdout and stderr contents
   */
  protected function runInNewRuntime($src) {
    return with (Runtime::getInstance()->newInstance(null, 'class', 'xp.runtime.Evaluate', []), function($p) use($src) {
      $p->in->write($src);
      $p->in->close();

      // Read output
      $out= $err= '';
      while ($b= $p->out->read()) { $out.= $b; }
      while ($b= $p->err->read()) { $err.= $b; }

      // Close child process
      $exitv= $p->close();
      return [$exitv, $out, $err];
    });
  }
  
  #[@test]
  public function new_class_with_empty_body() {
    $o= newinstance(Name::class, ['Test']);
    $this->assertInstanceOf(Name::class, $o);
  }

  #[@test]
  public function new_class_with_empty_body_as_string() {
    $o= newinstance(Name::class, ['Test'], '{}');
    $this->assertInstanceOf(Name::class, $o);
  }

  #[@test]
  public function new_class_with_empty_body_as_closuremap() {
    $o= newinstance(Name::class, ['Test'], []);
    $this->assertInstanceOf(Name::class, $o);
  }

  #[@test]
  public function new_class_with_member_as_string() {
    $o= newinstance(Name::class, ['Test'], '{
      public $test= "Test";
    }');
    $this->assertEquals('Test', $o->test);
  }

  #[@test]
  public function new_class_with_member_as_closuremap() {
    $o= newinstance(Name::class, ['Test'], [
      'test' => 'Test'
    ]);
    $this->assertEquals('Test', $o->test);
  }

  #[@test]
  public function new_class_with_annotations() {
    $o= newinstance('#[@test] net.xp_framework.unittest.Name', ['Test']);
    $this->assertTrue(typeof($o)->hasAnnotation('test'));
  }

  #[@test]
  public function new_class_with_field_annotations() {
    $o= newinstance(Name::class, ['Test'], [
      '#[@test] fixture' => null
    ]);
    $this->assertTrue(typeof($o)->getField('fixture')->hasAnnotation('test'));
  }

  #[@test]
  public function new_class_with_method_annotations() {
    $o= newinstance(Name::class, ['Test'], [
      '#[@test] fixture' => function() { }
    ]);
    $this->assertTrue(typeof($o)->getMethod('fixture')->hasAnnotation('test'));
  }

  #[@test]
  public function new_interface_with_body_as_string() {
    $o= newinstance(Runnable::class, [], '{ public function run() { } }');
    $this->assertInstanceOf(Runnable::class, $o);
  }

  #[@test]
  public function new_interface_with_body_as_closuremap() {
    $o= newinstance(Runnable::class, [], [
      'run' => function() { }
    ]);
    $this->assertInstanceOf(Runnable::class, $o);
  }

  #[@test]
  public function new_interface_with_single_function() {
    $o= newinstance(Runnable::class, [], function() { });
    $this->assertInstanceOf(Runnable::class, $o);
  }

  #[@test, @expect(ClassFormatException::class)]
  public function cannot_use_single_function_with_multi_method_interface() {
    newinstance(Value::class, [], function() { });
  }

  #[@test]
  public function new_interface_with_annotations() {
    $o= newinstance('#[@test] lang.Runnable', [], [
      'run' => function() { }
    ]);
    $this->assertTrue(typeof($o)->hasAnnotation('test'));
  }

  #[@test]
  public function new_trait_with_body_as_string() {
    $o= newinstance(Named::class, ['Test'], '{
      public function __construct($name) { $this->name= $name; }
    }');
    $this->assertEquals('Test', $o->name());
  }

  #[@test]
  public function new_trait_with_body_as_closuremap() {
    $o= newinstance(Named::class, ['Test'], [
      '__construct' => function($name) { $this->name= $name; }
    ]);
    $this->assertEquals('Test', $o->name());
  }

  #[@test]
  public function new_trait_with_annotations() {
    $o= newinstance('#[@test] net.xp_framework.unittest.core.Named', [], [
      'run' => function() { }
    ]);
    $this->assertTrue(typeof($o)->hasAnnotation('test'));
  }

  #[@test]
  public function new_trait_with_constructor() {
    $o= newinstance(ListOf::class, [[1, 2, 3]], []);
    $this->assertEquals([1, 2, 3], $o->elements());
  }

  #[@test]
  public function arguments_are_passed_to_constructor() {
    $instance= newinstance(Name::class, [$this], '{
      public $test= null;
      public function __construct($test) {
        $this->test= $test;
      }
    }');
    $this->assertEquals($this, $instance->test);
  }

  #[@test]
  public function arguments_are_passed_to_constructor_in_closuremap() {
    $instance= newinstance(Name::class, [$this], [
      'test' => null,
      '__construct' => function($test) {
        $this->test= $test;
      }
    ]);
    $this->assertEquals($this, $instance->test);
  }

  #[@test]
  public function arguments_are_passed_to_base_constructor_in_closuremap() {
    $base= ClassLoader::defineClass(nameof($this).'_BaseFixture', Name::class, [], [
      'test' => null,
      '__construct' => function($test) {
        $this->test= $test;
      }
    ]);
    $this->assertEquals($this, newinstance($base->getName(), [$this], [])->test);
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function variadic_argument_passing() {
    $r= $this->runInNewRuntime('
      class Test {
        public function verify() {
          $r= newinstance("lang.Runnable", [1, 2, 3], [
            "elements" => [],
            "__construct" => function(... $initial) { $this->elements= $initial; },
            "run" => function() { }
          ]);
          echo "OK: ", implode(", ", $r->elements);
        }
      }
      (new Test())->verify();
    ');
    $this->assertEquals(0, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], 'OK: 1, 2, 3'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function typed_variadic_argument_passing() {
    $r= $this->runInNewRuntime('
      class Test {
        public function verify() {
          $r= newinstance("lang.Runnable", [1, 2, 3], [
            "elements" => [],
            "__construct" => function(int... $initial) { $this->elements= $initial; },
            "run" => function() { }
          ]);
          echo "OK: ", implode(", ", $r->elements);
        }
      }
      (new Test())->verify();
    ');
    $this->assertEquals(0, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], 'OK: 1, 2, 3'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function missingMethodImplementationFatals() {
    $r= $this->runInNewRuntime('
      newinstance("lang.Runnable", [], "{}");
    ');
    $this->assertEquals(255, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], 'Fatal error'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function syntaxErrorFatals() {
    $r= $this->runInNewRuntime('
      newinstance("lang.Runnable", [], "{ @__SYNTAX ERROR__@ }");
    ');
    $this->assertEquals(255, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], "error, unexpected '@'"),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function missingClassFatals() {
    $r= $this->runInNewRuntime('
      newinstance("lang.NonExistantClass", [], "{}");
    ');
    $this->assertEquals(255, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], 'Class "lang.NonExistantClass" could not be found'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function notPreviouslyDefinedClassIsLoaded() {
    $r= $this->runInNewRuntime('
      if (isset(xp::$cl["lang.Runnable"])) {
        throw new \lang\IllegalStateException("Class lang.Runnable may not have been previously loaded");
      }
      $r= newinstance("lang.Runnable", [], "{ public function run() { echo \"Hi\"; } }");
      $r->run();
    ');
    $this->assertEquals(0, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], 'Hi'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test]
  public function packageOfNewInstancedClass() {
    $i= newinstance(Name::class, ['Test'], '{}');
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest'),
      typeof($i)->getPackage()
    );
  }

  #[@test, @values(['php.IteratorAggregate', 'IteratorAggregate'])]
  public function packageOfNewInstancedPHPClass($class) {
    $i= newinstance($class, [], '{ public function getIterator() { /* Empty */ }}');
    $this->assertEquals(
      Package::forName(''),
      typeof($i)->getPackage()
    );
  }

  #[@test, @values(['net.xp_framework.unittest.core.NamespacedClass', NamespacedClass::class])]
  public function packageOfNewInstancedNamespacedClass($class) {
    $i= newinstance($class, [], '{}');
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.core'),
      typeof($i)->getPackage()
    );
  }

  #[@test]
  public function packageOfNewInstancedNamespacedInterface() {
    $i= newinstance(NamespacedInterface::class, [], '{}');
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.core'),
      typeof($i)->getPackage()
    );
  }

  #[@test]
  public function className() {
    $instance= newinstance(Name::class, ['Test'], '{ }');
    $n= nameof($instance);
    $this->assertEquals(
      'net.xp_framework.unittest.Name',
      substr($n, 0, strrpos($n, "\xb7")),
      $n
    );
  }

  #[@test]
  public function anonymousClassWithoutConstructor() {
    newinstance(Runnable::class, [], '{
      public function run() {}
    }');
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function anonymousClassWithoutConstructorRaisesWhenArgsGiven() {
    newinstance(Runnable::class, ['arg1'], '{
      public function run() {}
    }');
  }

  #[@test]
  public function anonymousClassWithConstructor() {
    newinstance(Runnable::class, ['arg1'], '{
      public function __construct($arg) {
        if ($arg !== "arg1") {
          throw new \\unittest\\AssertionFailedError("equals", $arg, "arg1");
        }
      }
      public function run() {}
    }');
  }

  #[@test]
  public function this_can_be_accessed() {
    $instance= newinstance(Name::class, ['Test'], [
      'test'    => null,
      'setTest' => function($test) {
        $this->test= $test;
      },
      'getTest' => function() {
        return $this->test;
      }
    ]);

    $instance->setTest('Test');
    $this->assertEquals('Test', $instance->getTest());
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_array_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(array $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(array $args) { return "Hello"; }]);
      echo $instance->fixture([]);
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_callable_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(callable $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(callable $args) { return "Hello"; }]);
      echo $instance->fixture(function() { });
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_class_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(\lang\Runnable $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(\lang\Runnable $args) { return "Hello"; }]);
      echo $instance->fixture(new class() implements \lang\Runnable {
        public function run() { }
      });
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_self_return_type() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(): self;
        public function greeting() { return "Hello"; }
      }
      $instance= newinstance("Base", [], ["fixture" => function(): \Base { return new self(); }]);
      echo $instance->fixture()->greeting();
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_self_param_type() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(self $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(\Base $args) { return "Hello"; }]);
      echo $instance->fixture($instance);
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_primitive_param_type() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(string $arg);
      }
      $instance= newinstance("Base", [], ["fixture" => function(string $arg) { return $arg; }]);
      echo $instance->fixture("Hello");
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_primitive_return_type() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture($arg): string;
      }
      $instance= newinstance("Base", [], ["fixture" => function($arg): string { return $arg; }]);
      echo $instance->fixture("Hello");
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action([
  #  new VerifyThat('processExecutionEnabled'),
  #  new RuntimeVersion('>=7.2')
  #])]
  public function declaration_with_nullable_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(string $arg);
      }
      $instance= newinstance("Base", [], ["fixture" => function(?string $arg) { return $arg; }]);
      echo $instance->fixture("Hello");
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action([
  #  new VerifyThat('processExecutionEnabled'),
  #  new RuntimeVersion('>=7.1')
  #])]
  public function declaration_with_iterable_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(iterable $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(iterable $arg) { return "Hello"; }]);
      echo $instance->fixture([1, 2, 3]);
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action([
  #  new VerifyThat('processExecutionEnabled'),
  #  new RuntimeVersion('>=7.2')
  #])]
  public function declaration_with_object_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(object $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(object $arg) { return "Hello"; }]);
      echo $instance->fixture($instance);
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action([
  #  new VerifyThat('processExecutionEnabled'),
  #  new RuntimeVersion('>=7.1')
  #])]
  public function declaration_with_void_return() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(): void;
      }
      $instance= newinstance("Base", [], ["fixture" => function(): void { }]);
      $instance->fixture($instance);
      echo "Hello";
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_variadic() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(... $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(... $args) { return implode(" ", $args); }]);
      return $instance->fixture("Hello", "World");
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello World'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function declaration_with_typed_variadic() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(string... $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(string... $args) { return implode(" ", $args); }]);
      return $instance->fixture("Hello", "World");
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello World'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[@test, @action(new VerifyThat('processExecutionEnabled'))]
  public function value_types_fully_qualified() {
    $r= $this->runInNewRuntime('namespace test;
      abstract class Base {
        public abstract function reference(self $self): self;
        public function toString() { return "Hello World"; }
      }
      $instance= newinstance(Base::class, [], ["reference" => function(Base $self): Base { return $self; }]);
      return $instance->reference($instance)->toString();
    ');
    $this->assertEquals(
      ['exitcode' => 0, 'output' => 'Hello World'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }
}
