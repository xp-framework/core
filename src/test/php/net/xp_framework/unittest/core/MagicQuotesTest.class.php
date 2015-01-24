<?php namespace net\xp_framework\unittest\core;

use net\xp_framework\unittest\IgnoredOnHHVM;
use lang\Process;
use lang\Runtime;
use unittest\PrerequisitesNotMetError;

/**
 * Testcase ensuring XP refuses to start when magic_quotes_gpc is enabled.
 *
 * As of PHP 5.4, magic quotes have been removed and enabling them will
 * cause PHP to issue a fatal error.
 *
 * @see   http://svn.php.net/viewvc/php/php-src/branches/PHP_5_4/NEWS?revision=318433&view=markup
 * @see   php://magic_quotes
 */
class MagicQuotesTest extends \unittest\TestCase {

  /**
   * Skips tests if process execution has been disabled.
   */
  #[@beforeClass]
  public static function verifyProcessExecutionEnabled() {
    if (Process::$DISABLED) {
      throw new PrerequisitesNotMetError('Process execution disabled', NULL, array('enabled'));
    }
  }

  #[@test, @action(new IgnoredOnHHVM())]
  public function phpRefusesToStart() {
    $runtime= Runtime::getInstance();
    $options= $runtime->startupOptions()->withSetting('magic_quotes_gpc', 1)->withSetting('error_reporting', 'E_ALL');
    $out= $err= '';
    with ($p= $runtime->newInstance($options, 'class', 'xp.runtime.Evaluate', array('return 1;'))); {
      $p->in->close();

      // Read output
      while ($b= $p->out->read()) { $out.= $b; }
      while ($b= $p->err->read()) { $err.= $b; }

      // Close child process
      $exitv= $p->close();
    }
    $this->assertEquals(1, $exitv, 'exitcode');
    $this->assertTrue((bool)strstr($out.$err, "Directive 'magic_quotes_gpc' is no longer available in PHP"), \xp::stringOf([
      'out' => $out,
      'err' => $err
    ]));
  }
}
