<?php namespace lang\unittest;

use lang\{Process, Runtime};
use test\{Assert, Before, Ignore, PrerequisitesNotMetError, Test, Values};

class RuntimeInstantiationTest {

  /**
   * Skips tests if process execution has been disabled.
   */
  #[Before]
  public static function verifyProcessExecutionEnabled() {
    if (Process::$DISABLED) {
      throw new PrerequisitesNotMetError('Process execution disabled', null, ['enabled']);
    }
    if (strstr(php_uname('v'), 'Windows Server 2016')) {
      throw new PrerequisitesNotMetError('Process execution bug on Windows Server 2016', null, ['enabled']);
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

  #[Test, Ignore('Enable to see information')]
  public function displayCmdLineEnvironment() {
    echo $this->runInNewRuntime(Runtime::getInstance()->startupOptions(), '
      echo getenv("XP_CMDLINE");
    ');
  }

  #[Test]
  public function shutdownHookRunOnScriptEnd() {
    Assert::equals(
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

  #[Test]
  public function shutdownHookRunOnNormalExit() {
    Assert::equals(
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

  #[Test]
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
    Assert::equals('+OK exiting', substr($out, 0, 11), $out);
    Assert::equals('+OK Shutdown hook run', substr($out, -21), $out);
  }

  #[Test]
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
    Assert::equals('+OK exiting', substr($out, 0, 11), $out);
    Assert::equals('+OK Shutdown hook run', substr($out, -21), $out);
  }

  #[Test, Values([[[], '1: xp.runtime.Evaluate'], [['test'], '2: xp.runtime.Evaluate test']])]
  public function pass_arguments($args, $expected) {
    $out= $this->runInNewRuntime(
      Runtime::getInstance()->startupOptions(),
      'echo sizeof($argv), ": ", implode(" ", $argv);',
      0,
      $args
    );
    Assert::equals($expected, $out);
  }

  #[Test, Values([[['mysql+x://test@127.0.0.1/test'], '2: xp.runtime.Evaluate mysql+x://test@127.0.0.1/test'], [['über', '€uro'], '3: xp.runtime.Evaluate über €uro']])]
  public function pass_arguments_which_need_encoding($args, $expected) {
    $out= $this->runInNewRuntime(
      Runtime::getInstance()->startupOptions(),
      'echo sizeof($argv), ": ", implode(" ", $argv);',
      0,
      $args
    );
    Assert::equals($expected, $out);
  }
}