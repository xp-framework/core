<?php namespace net\xp_framework\unittest\tests;

use xp\unittest\Runner;
use io\streams\MemoryInputStream;
use io\streams\MemoryOutputStream;
use lang\ClassLoader;

/**
 * TestCase
 *
 * @see  xp://xp.unittest.Runner
 */
class UnittestRunnerTest extends \unittest\TestCase {
  private $runner, $out, $err;

  /**
   * Sets up test case
   */
  public function setUp() {
    $this->runner= new Runner();
    $this->out= $this->runner->setOut(new MemoryOutputStream());
    $this->err= $this->runner->setErr(new MemoryOutputStream());
  }

  /**
   * Asserts a given output stream contains the given bytes       
   *
   * @param   io.streams.MemoryOutputStream $m
   * @param   string $bytes
   * @param   string $message
   * @throws  unittest.AssertionFailedError
   */
  protected function assertOnStream(MemoryOutputStream $m, $bytes, $message= 'Not contained') {
    strstr($m->getBytes(), $bytes) || $this->fail($message, $m->getBytes(), $bytes);
  }

  #[@test]
  public function selfUsage() {
    $return= $this->runner->run([]);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, 'Usage:');
    $this->assertEquals('', $this->out->getBytes());
  }

  #[@test]
  public function helpParameter() {
    $return= $this->runner->run(['-?']);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, 'Usage:');
    $this->assertEquals('', $this->out->getBytes());
  }

  #[@test]
  public function noTests() {
    $return= $this->runner->run(['-v']);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, '*** No tests specified');
    $this->assertEquals('', $this->out->getBytes());
  }

  #[@test]
  public function nonExistantClass() {
    $return= $this->runner->run(['@@NON-EXISTANT@@']);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, '*** Class "@@NON-EXISTANT@@" could not be found');
    $this->assertEquals('', $this->out->getBytes());
  }

  #[@test]
  public function nonExistantFile() {
    $return= $this->runner->run(['@@NON-EXISTANT@@'.\xp::CLASS_FILE_EXT]);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, '@@NON-EXISTANT@@.class.php" does not exist!');
    $this->assertEquals('', $this->out->getBytes());
  }

  #[@test]
  public function nonExistantPackage() {
    $return= $this->runner->run(['@@NON-EXISTANT@@.*']);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, '*** No classloaders provide @@NON-EXISTANT@@');
    $this->assertEquals('', $this->out->getBytes());
  }

  #[@test]
  public function nonExistantPackageRecursive() {
    $return= $this->runner->run(['@@NON-EXISTANT@@.**']);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, '*** No classloaders provide @@NON-EXISTANT@@');
    $this->assertEquals('', $this->out->getBytes());
  }

  #[@test]
  public function nonExistantProperties() {
    $return= $this->runner->run(['@@NON-EXISTANT@@.ini']);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, '*** File "@@NON-EXISTANT@@.ini" not found');
    $this->assertEquals('', $this->out->getBytes());
  }

  #[@test]
  public function runEmptyTest() {
    $command= newinstance('unittest.TestCase', [$this->name]);
    $return= $this->runner->run([$command->getClassName()]);
    $this->assertEquals(3, $return);
    $this->assertOnStream($this->err, '*** Warning: No tests found in');
    $this->assertEquals('', $this->out->getBytes());
  }

  #[@test]
  public function runNonTest() {
    $return= $this->runner->run(['lang.Object']);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, '*** Error: Given argument is not a TestCase class (lang.XPClass<lang.Object>)');
    $this->assertEquals('', $this->out->getBytes());
  }

  #[@test]
  public function runSucceedingTest() {
    $command= newinstance('unittest.TestCase', ['succeeds'], [
      '#[@test] succeeds' => function() { $this->assertTrue(true); }
    ]);
    $return= $this->runner->run([$command->getClassName()]);
    $this->assertEquals(0, $return);
    $this->assertEquals('', $this->err->getBytes());
    $this->assertOnStream($this->out, '1/1 run (0 skipped), 1 succeeded, 0 failed');
  }

  #[@test]
  public function runColoredTest($setting= '--color=on') {
    $command= newinstance('unittest.TestCase', ['succeeds'], [
      '#[@test] succeeds' => function() { $this->assertTrue(true); }
    ]);
    $return= $this->runner->run([$setting, $command->getClassName()]);
    $this->assertEquals(0, $return);
    $this->assertEquals('', $this->err->getBytes());
    $this->assertOnStream($this->out, '1/1 run (0 skipped), 1 succeeded, 0 failed');
  }

  #[@test]
  public function runNocolorTest() {
    $this->runColoredTest('--color=off');
  }

  #[@test]
  public function runAutocolorTest() {
    $this->runColoredTest('--color=auto');
  }


  #[@test]
  public function runShortAutocolorTest() {
    $this->runColoredTest('--color');
  }

  #[@test]
  public function runUnsupportedColorSettingTestFails() {
    $command= newinstance('unittest.TestCase', ['succeeds'], [
      '#[@test] succeeds' => function() { $this->assertTrue(true); }
    ]);
    $return= $this->runner->run(['--color=anything', $command->getClassName()]);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, '*** Unsupported argument for --color');
  }

  #[@test]
  public function runFailingTest() {
    $command= newinstance('unittest.TestCase', ['fails'], [
      '#[@test] fails' => function() { $this->assertTrue(false); }
    ]);
    $return= $this->runner->run([$command->getClassName()]);
    $this->assertEquals(1, $return);
    $this->assertEquals('', $this->err->getBytes());
    $this->assertOnStream($this->out, '1/1 run (0 skipped), 0 succeeded, 1 failed');
  }

  #[@test, @values([[['-e']], [['-e', '-']]])]
  public function evaluateReadsCodeFromStdIn($args) {
    $this->runner->setIn(new MemoryInputStream('$this->assertTrue(true);'));
    $return= $this->runner->run($args);
    $this->assertEquals(0, $return);
    $this->assertEquals('', $this->err->getBytes());
    $this->assertOnStream($this->out, '1/1 run (0 skipped), 1 succeeded, 0 failed');
  }

  #[@test, @values([
  #  '$this->assertTrue(true)',
  #  '$this->assertTrue(true);',
  #  '<?php $this->assertTrue(true);'
  #])]
  public function evaluateSucceedingTest($code) {
    $return= $this->runner->run(['-e', $code]);
    $this->assertEquals(0, $return);
    $this->assertEquals('', $this->err->getBytes());
    $this->assertOnStream($this->out, '1/1 run (0 skipped), 1 succeeded, 0 failed');
  }

  #[@test]
  public function evaluateFailingTest() {
    $return= $this->runner->run(['-e', '$this->assertTrue(false);']);
    $this->assertEquals(1, $return);
    $this->assertEquals('', $this->err->getBytes());
    $this->assertOnStream($this->out, '1/1 run (0 skipped), 0 succeeded, 1 failed');
  }

  #[@test]
  public function runSingleTest() {
    $command= newinstance('unittest.TestCase', ['succeeds'], [
      '#[@test] succeeds' => function() { $this->assertTrue(true); }
    ]);
    $return= $this->runner->run([$command->getClassName().'::succeeds']);
    $this->assertEquals(0, $return);
    $this->assertEquals('', $this->err->getBytes());
  }

  #[@test]
  public function runSingleTestWrongSpec() {
    $command= newinstance('unittest.TestCase', ['succeeds'], [
      '#[@test] succeeds' => function() { $this->assertTrue(true); }
    ]);
    $return= $this->runner->run([$command->getClassName().'::succeed']);
    $this->assertEquals(2, $return);
    $this->assertOnStream($this->err, '*** Error: Test method does not exist: succeed()');
  }

  #[@test]
  public function withListener() {
    $class= ClassLoader::getDefault()->defineClass('net.xp_framework.unittest.tests.WithListenerTestFixture', 'xp.unittest.DefaultListener', [], '{
      public static $options= [];
    }');

    $return= $this->runner->run(['-l', $class->getName(), '-']);
    $this->assertEquals(
      [], 
      $class->getField('options')->get(null)
    );
  }

  #[@test]
  public function withListenerOptions() {
    $class= ClassLoader::getDefault()->defineClass('net.xp_framework.unittest.tests.WithListenerOptionsTestFixture', 'xp.unittest.DefaultListener', [], '{
      public static $options= [];
      #[@arg]
      public function setOption($value) { self::$options[__FUNCTION__]= $value; }
      #[@arg]
      public function setVerbose() { self::$options[__FUNCTION__]= true; }
    }');

    $return= $this->runner->run(['-l', $class->getName(), '-', '-o', 'option', 'value', '-o', 'v']);
    $this->assertEquals(
      ['setOption' => 'value', 'setVerbose' => true], 
      $class->getField('options')->get(null)
    );
  }

  #[@test]
  public function withLongListenerOption() {
    $class= ClassLoader::getDefault()->defineClass('net.xp_framework.unittest.tests.WithLongListenerOptionTestFixture', 'xp.unittest.DefaultListener', [], '{
      public static $options= [];
      #[@arg]
      public function setOption($value) { self::$options[__FUNCTION__]= $value; }
    }');

    $return= $this->runner->run(['-l', $class->getName(), '-', '-o', 'option', 'value']);
    $this->assertEquals(
      ['setOption' => 'value'], 
      $class->getField('options')->get(null)
    );
  }

  #[@test]
  public function withNamedLongListenerOption() {
    $class= ClassLoader::getDefault()->defineClass('net.xp_framework.unittest.tests.WithNamedLongListenerOptionTestFixture', 'xp.unittest.DefaultListener', [], '{
      public static $options= [];
      #[@arg(name = "use")]
      public function setOption($value) { self::$options[__FUNCTION__]= $value; }
    }');

    $return= $this->runner->run(['-l', $class->getName(), '-', '-o', 'use', 'value']);
    $this->assertEquals(
      ['setOption' => 'value'], 
      $class->getField('options')->get(null)
    );
  }

  #[@test]
  public function withNamedLongListenerOptionShort() {
    $class= ClassLoader::getDefault()->defineClass('net.xp_framework.unittest.tests.WithNamedLongListenerOptionShortTestFixture', 'xp.unittest.DefaultListener', [], '{
      public static $options= [];
      #[@arg(name = "use")]
      public function setOption($value) { self::$options[__FUNCTION__]= $value; }
    }');

    $return= $this->runner->run(['-l', $class->getName(), '-', '-o', 'u', 'value']);
    $this->assertEquals(
      ['setOption' => 'value'],
      $class->getField('options')->get(null)
    );
  }    

  #[@test]
  public function withShortListenerOption() {
    $class= ClassLoader::getDefault()->defineClass('net.xp_framework.unittest.tests.WithShortListenerOptionTestFixture', 'xp.unittest.DefaultListener', [], '{
      public static $options= [];
      #[@arg]
      public function setOption($value) { self::$options[__FUNCTION__]= $value; }
    }');

    $return= $this->runner->run(['-l', $class->getName(), '-', '-o', 'o', 'value']);
    $this->assertEquals(
      ['setOption' => 'value'],
      $class->getField('options')->get(null)
    );
  }

  #[@test]
  public function withNamedShortListenerOption() {
    $class= ClassLoader::getDefault()->defineClass('net.xp_framework.unittest.tests.WithNamedShortListenerOptionTestFixture', 'xp.unittest.DefaultListener', [], '{
      public static $options= [];
      #[@arg(short = "O")]
      public function setOption($value) { self::$options[__FUNCTION__]= $value; }
    }');

    $return= $this->runner->run(['-l', $class->getName(), '-', '-o', 'O', 'value']);
    $this->assertEquals(
      ['setOption' => 'value'],
      $class->getField('options')->get(null)
    );
  }

  #[@test]
  public function withPositionalOptionListenerOption() {
    $class= ClassLoader::getDefault()->defineClass('net.xp_framework.unittest.tests.WithPositionalOptionTestFixture', 'xp.unittest.DefaultListener', [], '{
      public static $options= [];
      #[@arg(position= 0)]
      public function setOption($value) { self::$options[__FUNCTION__]= $value; }
    }');

    $return= $this->runner->run(['-l', $class->getName(), '-', '-o', 'value']);
    $this->assertEquals(
      ['setOption' => 'value'],
      $class->getField('options')->get(null)
    );
  }
}
