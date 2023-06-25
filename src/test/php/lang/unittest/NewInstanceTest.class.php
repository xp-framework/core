<?php namespace lang\unittest;

use ReturnTypesWillChange;
use lang\reflect\Package;
use lang\{ClassFormatException, ClassLoader, IllegalAccessException, Process, Runnable, Value};
use lang\{Runtime as XPRuntime, Reflection};
use test\verify\{Condition, Runtime};
use test\{Assert, Expect, Test, Values};
use util\Objects;

class NewInstanceTest {

  /** @return bool */
  protected static function processExecutionEnabled() {
    return !Process::$DISABLED && !strstr(php_uname('v'), 'Windows Server 2016');
  }

  /**
   * Runs sourcecode inside a new runtime
   *
   * @param   string $src
   * @return  var[] an array with three elements: exitcode, stdout and stderr contents
   */
  protected function runInNewRuntime($src) {
    return with (XPRuntime::getInstance()->newInstance(null, 'class', 'xp.runtime.Evaluate', []), function($p) use($src) {
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
  
  #[Test]
  public function new_class_with_empty_body() {
    $o= newinstance(Name::class, ['Test']);
    Assert::instance(Name::class, $o);
  }

  #[Test]
  public function new_class_with_empty_body_as_string() {
    $o= newinstance(Name::class, ['Test'], '{}');
    Assert::instance(Name::class, $o);
  }

  #[Test]
  public function new_class_with_empty_body_as_closuremap() {
    $o= newinstance(Name::class, ['Test'], []);
    Assert::instance(Name::class, $o);
  }

  #[Test]
  public function new_class_with_member_as_string() {
    $o= newinstance(Name::class, ['Test'], '{
      public $test= "Test";
    }');
    Assert::equals('Test', $o->test);
  }

  #[Test]
  public function new_class_with_member_as_closuremap() {
    $o= newinstance(Name::class, ['Test'], [
      'test' => 'Test'
    ]);
    Assert::equals('Test', $o->test);
  }

  #[Test]
  public function new_class_with_annotations() {
    $o= newinstance('#[Test] lang.unittest.Name', ['Test']);
    Assert::equals('test', Reflection::type($o)->annotation('lang.unittest.Test')->name());
  }

  #[Test]
  public function new_class_with_field_annotations() {
    $o= newinstance(Name::class, ['Test'], [
      '#[Test] fixture' => null
    ]);
    Assert::equals('test', Reflection::type($o)->property('fixture')->annotation('lang.unittest.Test')->name());
  }

  #[Test]
  public function new_class_with_method_annotations() {
    $o= newinstance(Name::class, ['Test'], [
      '#[Test] fixture' => function() { }
    ]);
    Assert::equals('test', Reflection::type($o)->method('fixture')->annotation('lang.unittest.Test')->name());
  }

  #[Test]
  public function new_interface_with_body_as_string() {
    $o= newinstance(Runnable::class, [], '{ public function run() { } }');
    Assert::instance(Runnable::class, $o);
  }

  #[Test]
  public function new_interface_with_body_as_closuremap() {
    $o= newinstance(Runnable::class, [], [
      'run' => function() { }
    ]);
    Assert::instance(Runnable::class, $o);
  }

  #[Test]
  public function new_interface_with_single_function() {
    $o= newinstance(Runnable::class, [], function() { });
    Assert::instance(Runnable::class, $o);
  }

  #[Test]
  public function new_abstract_class_with_single_function() {
    $o= newinstance(BinaryOp::class, ['+'], function($a, $b) { return $a + $b; });
    Assert::instance(BinaryOp::class, $o);
  }

  #[Test, Expect(ClassFormatException::class)]
  public function cannot_use_single_function_with_multi_method_interface() {
    newinstance(Value::class, [], function() { });
  }

  #[Test]
  public function new_interface_with_annotations() {
    $o= newinstance('#[Test] lang.Runnable', [], [
      'run' => function() { }
    ]);
    Assert::true(typeof($o)->hasAnnotation('test'));
  }

  #[Test]
  public function new_trait_with_body_as_string() {
    $o= newinstance(Named::class, ['Test'], '{
      public function __construct($name) { $this->name= $name; }
    }');
    Assert::equals('Test', $o->name());
  }

  #[Test]
  public function new_trait_with_body_as_closuremap() {
    $o= newinstance(Named::class, ['Test'], [
      '__construct' => function($name) { $this->name= $name; }
    ]);
    Assert::equals('Test', $o->name());
  }

  #[Test]
  public function new_trait_with_annotations() {
    $o= newinstance('#[Test] lang.unittest.Named', [], [
      'run' => function() { }
    ]);
    Assert::true(typeof($o)->hasAnnotation('test'));
  }

  #[Test]
  public function new_trait_with_constructor() {
    $o= newinstance(Listing::class, [[1, 2, 3]], []);
    Assert::equals([1, 2, 3], $o->elements());
  }

  #[Test]
  public function arguments_are_passed_to_constructor() {
    $instance= newinstance(Name::class, [$this], '{
      public $test= null;
      public function __construct($test) {
        $this->test= $test;
      }
    }');
    Assert::equals($this, $instance->test);
  }

  #[Test]
  public function arguments_are_passed_to_constructor_in_closuremap() {
    $instance= newinstance(Name::class, [$this], [
      'test' => null,
      '__construct' => function($test) {
        $this->test= $test;
      }
    ]);
    Assert::equals($this, $instance->test);
  }

  #[Test]
  public function arguments_are_passed_to_base_constructor_in_closuremap() {
    $base= ClassLoader::defineClass(nameof($this).'_BaseFixture', Name::class, [], [
      'test' => null,
      '__construct' => function($test) {
        $this->test= $test;
      }
    ]);
    Assert::equals($this, newinstance($base->getName(), [$this], [])->test);
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
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
    Assert::equals(0, $r[0], 'exitcode');
    Assert::true(
      (bool)strstr($r[1].$r[2], 'OK: 1, 2, 3'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
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
    Assert::equals(0, $r[0], 'exitcode');
    Assert::true(
      (bool)strstr($r[1].$r[2], 'OK: 1, 2, 3'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function missingMethodImplementationFatals() {
    $r= $this->runInNewRuntime('
      newinstance("lang.Runnable", [], "{}");
    ');
    Assert::equals(255, $r[0], 'exitcode');
    Assert::true(
      (bool)strstr($r[1].$r[2], 'Fatal error'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function syntaxErrorFatals() {
    $r= $this->runInNewRuntime('
      newinstance("lang.Runnable", [], "{ @__SYNTAX ERROR__@ }");
    ');
    Assert::equals(255, $r[0], 'exitcode');
    Assert::true(
      (bool)preg_match('/syntax error, unexpected.+@/', $r[1].$r[2]),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function missingClassFatals() {
    $r= $this->runInNewRuntime('
      newinstance("lang.NonExistantClass", [], "{}");
    ');
    Assert::equals(255, $r[0], 'exitcode');
    Assert::true(
      (bool)strstr($r[1].$r[2], 'Class "lang.NonExistantClass" could not be found'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function notPreviouslyDefinedClassIsLoaded() {
    $r= $this->runInNewRuntime('
      if (isset(xp::$cl["lang.Runnable"])) {
        throw new \lang\IllegalStateException("Class lang.Runnable may not have been previously loaded");
      }
      $r= newinstance("lang.Runnable", [], "{ public function run() { echo \"Hi\"; } }");
      $r->run();
    ');
    Assert::equals(0, $r[0], 'exitcode');
    Assert::true(
      (bool)strstr($r[1].$r[2], 'Hi'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[Test]
  public function packageOfNewInstancedClass() {
    $i= newinstance(Name::class, ['Test'], '{}');
    Assert::equals(
      Package::forName('lang.unittest'),
      typeof($i)->getPackage()
    );
  }

  #[Test, Values(['php.IteratorAggregate', 'IteratorAggregate'])]
  public function packageOfNewInstancedPHPClass($class) {
    $i= newinstance($class, [], '{
      #[ReturnTypeWillChange]
      public function getIterator() { /* Empty */ }
    }');
    Assert::equals(
      Package::forName(''),
      typeof($i)->getPackage()
    );
  }

  #[Test, Values(['lang.unittest.NamespacedClass', NamespacedClass::class])]
  public function packageOfNewInstancedNamespacedClass($class) {
    $i= newinstance($class, [], '{}');
    Assert::equals(
      Package::forName('lang.unittest'),
      typeof($i)->getPackage()
    );
  }

  #[Test]
  public function packageOfNewInstancedNamespacedInterface() {
    $i= newinstance(NamespacedInterface::class, [], '{}');
    Assert::equals(
      Package::forName('lang.unittest'),
      typeof($i)->getPackage()
    );
  }

  #[Test]
  public function className() {
    $instance= newinstance(Name::class, ['Test'], '{ }');
    $n= nameof($instance);
    Assert::equals(
      'lang.unittest.Name',
      substr($n, 0, strrpos($n, "\xb7")),
      $n
    );
  }

  #[Test]
  public function anonymousClassWithoutConstructor() {
    newinstance(Runnable::class, [], '{
      public function run() {}
    }');
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function anonymousClassWithoutConstructorRaisesWhenArgsGiven() {
    newinstance(Runnable::class, ['arg1'], '{
      public function run() {}
    }');
  }

  #[Test]
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

  #[Test]
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
    Assert::equals('Test', $instance->getTest());
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function declaration_with_array_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(array $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(array $args) { return "Hello"; }]);
      echo $instance->fixture([]);
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function declaration_with_callable_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(callable $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(callable $args) { return "Hello"; }]);
      echo $instance->fixture(function() { });
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
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
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function declaration_with_self_return_type() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(): self;
        public function greeting() { return "Hello"; }
      }
      $instance= newinstance("Base", [], ["fixture" => function(): \Base { return new self(); }]);
      echo $instance->fixture()->greeting();
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function declaration_with_self_param_type() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(self $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(\Base $args) { return "Hello"; }]);
      echo $instance->fixture($instance);
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function declaration_with_primitive_param_type() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(string $arg);
      }
      $instance= newinstance("Base", [], ["fixture" => function(string $arg) { return $arg; }]);
      echo $instance->fixture("Hello");
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function declaration_with_primitive_return_type() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture($arg): string;
      }
      $instance= newinstance("Base", [], ["fixture" => function($arg): string { return $arg; }]);
      echo $instance->fixture("Hello");
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()'), Runtime(php: '>=7.2')]
  public function declaration_with_nullable_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(string $arg);
      }
      $instance= newinstance("Base", [], ["fixture" => function(?string $arg) { return $arg; }]);
      echo $instance->fixture("Hello");
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()'), Runtime(php: '>=7.1')]
  public function declaration_with_iterable_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(iterable $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(iterable $arg) { return "Hello"; }]);
      echo $instance->fixture([1, 2, 3]);
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()'), Runtime(php: '>=7.2')]
  public function declaration_with_object_typehint() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(object $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(object $arg) { return "Hello"; }]);
      echo $instance->fixture($instance);
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()'), Runtime(php: '>=7.1')]
  public function declaration_with_void_return() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(): void;
      }
      $instance= newinstance("Base", [], ["fixture" => function(): void { }]);
      $instance->fixture($instance);
      echo "Hello";
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function declaration_with_variadic() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(... $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(... $args) { return implode(" ", $args); }]);
      return $instance->fixture("Hello", "World");
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello World'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function declaration_with_typed_variadic() {
    $r= $this->runInNewRuntime('
      abstract class Base {
        public abstract function fixture(string... $args);
      }
      $instance= newinstance("Base", [], ["fixture" => function(string... $args) { return implode(" ", $args); }]);
      return $instance->fixture("Hello", "World");
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello World'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }

  #[Test, Condition(assert: 'self::processExecutionEnabled()')]
  public function value_types_fully_qualified() {
    $r= $this->runInNewRuntime('namespace test;
      abstract class Base {
        public abstract function reference(self $self): self;
        public function toString() { return "Hello World"; }
      }
      $instance= newinstance(Base::class, [], ["reference" => function(Base $self): Base { return $self; }]);
      return $instance->reference($instance)->toString();
    ');
    Assert::equals(
      ['exitcode' => 0, 'output' => 'Hello World'],
      ['exitcode' => $r[0], 'output' => $r[1].$r[2]]
    );
  }
}