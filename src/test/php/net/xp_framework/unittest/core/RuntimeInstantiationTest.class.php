<?php namespace net\xp_framework\unittest\core;

use lang\{Process, Runtime};
use unittest\{BeforeClass, Ignore, PrerequisitesNotMetError, Test, Values};

/**
 * TestCase
 *
 * @see  xp://lang.Runtime
 */
class RuntimeInstantiationTest extends \unittest\TestCase {

  /**
   * Skips tests if process execution has been disabled.
   */
  #[BeforeClass]
  public static function verifyProcessExecutionEnabled() {
    if (Process::$DISABLED) {
      throw new PrerequisitesNotMetError('Process execution disabled', null, ['enabled']);
    }
  }

  /**
   * Runs sourcecode in a new runtime
   *
   * @param  lang.RuntimeOptions $options
   * @param  string $src
   * @param  int $expectedExitCode default 0
   * @param  string[] $args
   * @throws lang.IllegalStateException if process exits with a non-zero exitcode
   * @return string output
   */
  protected function runInNewRuntime(\lang\RuntimeOptions $startup, $src, $expectedExitCode= 0, $args= []) {
    with ($out= $err= '', $p= Runtime::getInstance()->newInstance($startup, 'class', 'xp.runtime.Evaluate', array_merge(['--'], $args))); {
      $p->in->write('use lang\Runtime;');
      $p->in->write($src);
      $p->in->close();

      // Read output
      while ($b= $p->out->read()) { $out.= $b; }
      while ($b= $p->err->read()) { $err.= $b; }

      // Check for exitcode
      if ($expectedExitCode !== ($exitv= $p->close())) {
        throw new \lang\IllegalStateException(sprintf(
          "Command %s failed with exit code #%d (instead of %d) {OUT: %s\nERR: %s\n}",
          $p->getCommandLine(),
          $exitv,
          $expectedExitCode,
          $out,
          $err
        ));
      }
    }
    return $out;
  }

  #[Test]
  public function loadLoadedLibrary() {
    $this->assertEquals(
      '+OK No exception thrown',
      $this->runInNewRuntime(Runtime::getInstance()->startupOptions()->withSetting('enable_dl', 1), '
        try {
          Runtime::getInstance()->loadLibrary("standard");
          echo "+OK No exception thrown";
        } catch (\lang\Throwable $e) {
          echo "-ERR ".nameof($e);
        }
      ')
    );
  }

  #[Test]
  public function loadNonExistantLibrary() {
    $this->assertEquals(
      '+OK lang.ElementNotFoundException',
      $this->runInNewRuntime(Runtime::getInstance()->startupOptions()->withSetting('enable_dl', 1), '
        try {
          Runtime::getInstance()->loadLibrary(":DOES-NOT-EXIST");
          echo "-ERR No exception thrown";
        } catch (\lang\ElementNotFoundException $e) {
          echo "+OK ".nameof($e);
        }
      ')
    );
  }

  #[Test]
  public function loadLibraryWithoutEnableDl() {
    $this->assertEquals(
      '+OK lang.IllegalAccessException',
      $this->runInNewRuntime(Runtime::getInstance()->startupOptions()->withSetting('enable_dl', 0), '
        try {
          Runtime::getInstance()->loadLibrary("irrelevant");
          echo "-ERR No exception thrown";
        } catch (\lang\IllegalAccessException $e) {
          echo "+OK ".nameof($e);
        }
      ')
    );
  }

  #[Test, Ignore('Enable and edit library name to something loadable to see information')]
  public function displayInformation() {
    echo $this->runInNewRuntime(Runtime::getInstance()->startupOptions()->withSetting('enable_dl', 1), '
      try {
        $r= Runtime::getInstance()->loadLibrary("xsl");
        echo "+OK: ", $r ? "Loaded" : "Compiled";
      } catch (\lang\Throwable $e) {
        echo "-ERR ".$e->toString();
      }
    ');
  }

  #[Test, Ignore('Enable to see information')]
  public function displayCmdLineEnvironment() {
    echo $this->runInNewRuntime(Runtime::getInstance()->startupOptions(), '
      echo getenv("XP_CMDLINE");
    ');
  }

  #[Test, Ignore('https://github.com/xp-framework/core/pull/251')]
  public function shutdownHookRunOnScriptEnd() {
    $this->assertEquals(
      '+OK exiting, +OK Shutdown hook run',
      $this->runInNewRuntime(Runtime::getInstance()->startupOptions(), '
        Runtime::getInstance()->addShutdownHook(newinstance("lang.Runnable", [], "{
          public function run() {
            echo \'+OK Shutdown hook run\';
          }
        }"));

        echo "+OK exiting, ";
      ')
    );
  }

  #[Test, Ignore('https://github.com/xp-framework/core/pull/251')]
  public function shutdownHookRunOnNormalExit() {
    $this->assertEquals(
      '+OK exiting, +OK Shutdown hook run',
      $this->runInNewRuntime(Runtime::getInstance()->startupOptions(), '
        Runtime::getInstance()->addShutdownHook(newinstance("lang.Runnable", [], "{
          public function run() {
            echo \'+OK Shutdown hook run\';
          }
        }"));

        echo "+OK exiting, ";
        exit();
      ')
    );
  }

  #[Test, Ignore('https://github.com/xp-framework/core/pull/251')]
  public function shutdownHookRunOnFatal() {
    $out= $this->runInNewRuntime(Runtime::getInstance()->startupOptions(), '
      Runtime::getInstance()->addShutdownHook(newinstance("lang.Runnable", [], "{
        public function run() {
          echo \'+OK Shutdown hook run\';
        }
      }"));

      echo "+OK exiting";
      $fatal= NULL;
      $fatal->error();
    ', 255);
    $this->assertEquals('+OK exiting', substr($out, 0, 11), $out);
    $this->assertEquals('+OK Shutdown hook run', substr($out, -21), $out);
  }

  #[Test, Ignore('https://github.com/xp-framework/core/pull/251')]
  public function shutdownHookRunOnUncaughtException() {
    $out= $this->runInNewRuntime(Runtime::getInstance()->startupOptions(), '
      Runtime::getInstance()->addShutdownHook(newinstance("lang.Runnable", [], "{
        public function run() {
          echo \'+OK Shutdown hook run\';
        }
      }"));

      echo "+OK exiting";
      throw new \lang\Error("Uncaught");
    ', 255);
    $this->assertEquals('+OK exiting', substr($out, 0, 11), $out);
    $this->assertEquals('+OK Shutdown hook run', substr($out, -21), $out);
  }

  #[Test, Values([[[], '1: xp.runtime.Evaluate'], [['test'], '2: xp.runtime.Evaluate test']])]
  public function pass_arguments($args, $expected) {
    $out= $this->runInNewRuntime(
      Runtime::getInstance()->startupOptions(),
      'echo sizeof($argv), ": ", implode(" ", $argv);',
      0,
      $args
    );
    $this->assertEquals($expected, $out);
  }

  #[Test, Values([[['mysql+x://test@127.0.0.1/test'], '2: xp.runtime.Evaluate mysql+x://test@127.0.0.1/test'], [['über', '€uro'], '3: xp.runtime.Evaluate über €uro']])]
  public function pass_arguments_which_need_encoding($args, $expected) {
    $out= $this->runInNewRuntime(
      Runtime::getInstance()->startupOptions(),
      'echo sizeof($argv), ": ", implode(" ", $argv);',
      0,
      $args
    );
    $this->assertEquals($expected, $out);
  }
}