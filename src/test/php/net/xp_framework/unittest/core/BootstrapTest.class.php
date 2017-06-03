<?php namespace net\xp_framework\unittest\core;

use net\xp_framework\unittest\IgnoredOnHHVM;
use lang\{Runtime, RuntimeOptions, Process};
use unittest\PrerequisitesNotMetError;
use util\Objects;

/**
 * TestCase
 */
class BootstrapTest extends \unittest\TestCase {

  /**
   * Skips tests if process execution has been disabled.
   */
  #[@beforeClass]
  public static function verifyProcessExecutionEnabled() {
    if (Process::$DISABLED) {
      throw new PrerequisitesNotMetError('Process execution disabled', null, ['enabled']);
    }
  }

  /**
   * Create a new runtime
   *
   * @param   lang.RuntimeOptions $options
   * @return  var[] an array with three elements: exitcode, stdout and stderr contents
   */
  protected function runWith(RuntimeOptions $options) {
    with ($out= $err= '', $p= Runtime::getInstance()->newInstance($options, 'class', 'xp.runtime.Evaluate', ['return 1;'])); {
      $p->in->close();

      // Read output
      while ($b= $p->out->read()) { $out.= $b; }
      while ($b= $p->err->read()) { $err.= $b; }

      // Close child process
      $exitv= $p->close();
    }
    return [$exitv, $out, $err];
  }

  /**
   * Helper to run bootstrapping with given tz
   *
   * @param   string tz
   */
  protected function runWithTz($tz) {
    $r= $this->runWith(Runtime::getInstance()->startupOptions()->withSetting('date.timezone', $tz));
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], '[xp::core] date.timezone not configured properly.'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
    $this->assertEquals(255, $r[0], 'exitcode');
  }    
  
  #[@test, @action(new IgnoredOnHHVM())]
  public function fatalsForEmptyTimezone() {
    $this->runWithTz('');
  }

  #[@test, @action(new IgnoredOnHHVM())]
  public function fatalsForInvalidTimezone() {
    $this->runWithTz('Foo/bar');
  }

  #[@test]
  public function fatalsForNonExistingPaths() {
    $r= $this->runWith(Runtime::getInstance()->startupOptions()->withClassPath('/does-not-exist'));
    $this->assertEquals(255, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], '[bootstrap] Classpath element [/does-not-exist] not found'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test]
  public function fatalsForNonExistingXars() {
    $r= $this->runWith(Runtime::getInstance()->startupOptions()->withClassPath('/does-not-exist.xar'));
    $this->assertEquals(255, $r[0], 'exitcode');
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], '[bootstrap] Classpath element [/does-not-exist.xar] not found'),
      Objects::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }
}
