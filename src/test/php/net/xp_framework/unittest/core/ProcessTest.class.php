<?php namespace net\xp_framework\unittest\core;

use io\IOException;
use io\streams\{Streams, MemoryOutputStream};
use lang\{Runtime, System, Process, IllegalStateException};
use unittest\{PrerequisitesNotMetError, AssertionFailedError};

/**
 * TestCase for Process class
 *
 * @see   xp://lang.Process
 */
class ProcessTest extends \unittest\TestCase {

  /**
   * Skips tests if process execution has been disabled.
   *
   * @return void
   */
  #[@beforeClass]
  public static function verifyProcessExecutionEnabled() {
    if (Process::$DISABLED) {
      throw new PrerequisitesNotMetError('Process execution disabled', null, ['enabled']);
    }
  }

  /**
   * Return executable name
   *
   * @return string
   */
  private function executable() {
    return Runtime::getInstance()->getExecutable()->getFilename();
  }

  /**
   * Return executable arguments. Takes care of prepending `--php` for HHVM.
   *
   * @param   string... $args
   * @return  string[]
   */
  private function arguments(... $args) {
    if (defined('HHVM_VERSION')) {
      array_unshift($args, '--php');
    }
    return $args;
  }

  /**
   * Test process status information methods
   *
   * @see      xp://lang.Process#getProcessId
   * @see      xp://lang.Process#getFilename
   * @see      xp://lang.Process#getCommandLine
   * @see      xp://lang.Process#exitValue
   */
  #[@test]
  public function information() {
    $p= new Process($this->executable(), $this->arguments('-v'));
    try {
      $this->assertEquals(-1, $p->exitValue(), 'Process should not have exited yet');
      $this->assertNotEquals(0, $p->getProcessId());
      $this->assertNotEquals('', $p->getFilename());
      $this->assertNotEquals(false, strpos($p->getCommandLine(), '-v'));
      $p->close();
    } catch (AssertionFailedError $e) {
      $p->close();    // Ensure process is closed
      throw $e;
    }
  }

  #[@test]
  public function newInstance() {
    $p= Runtime::getInstance()->getExecutable()->newInstance($this->arguments('-v'));
    $version= defined('HHVM_VERSION') ? 'HipHop VM '.HHVM_VERSION : 'PHP '.PHP_VERSION;
    $this->assertEquals($version, $p->out->read(strlen($version)));
    $p->close();
  }

  #[@test]
  public function exitValueReturnedFromClose() {
    $p= new Process($this->executable(), $this->arguments('-r', 'exit(0);'));
    $this->assertEquals(0, $p->close());
  }

  #[@test]
  public function nonZeroExitValueReturnedFromClose() {
    $p= new Process($this->executable(), $this->arguments('-r', 'exit(2);'));
    $this->assertEquals(2, $p->close());
  }

  #[@test]
  public function exitValue() {
    $p= new Process($this->executable(), $this->arguments('-r', 'exit(0);'));
    $p->close();
    $this->assertEquals(0, $p->exitValue());
  }

  #[@test]
  public function nonZeroExitValue() {
    $p= new Process($this->executable(), $this->arguments('-r', 'exit(2);'));
    $p->close();
    $this->assertEquals(2, $p->exitValue());
  }

  #[@test]
  public function stdIn() {
    $p= new Process($this->executable(), $this->arguments('-r', 'fprintf(STDOUT, fread(STDIN, 0xFF));'));
    $p->in->write('IN');
    $p->in->close();
    $out= $p->out->read();
    $p->close();
    $this->assertEquals('IN', $out);
  }

  #[@test]
  public function stdOut() {
    $p= new Process($this->executable(), $this->arguments('-r', 'fprintf(STDOUT, "OUT");'));
    $out= $p->out->read();
    $p->close();
    $this->assertEquals('OUT', $out);
  }

  #[@test]
  public function stdErr() {
    $p= new Process($this->executable(), $this->arguments('-r', 'fprintf(STDERR, "ERR");'));
    $err= $p->err->read();
    $p->close();
    $this->assertEquals('ERR', $err);
  }

  #[@test, @expect(IOException::class)]
  public function runningNonExistantFile() {
    new Process(':FILE_DOES_NOT_EXIST:');
  }

  #[@test, @expect(IOException::class)]
  public function runningDirectory() {
    new Process(System::tempDir());
  }

  #[@test, @expect(IOException::class)]
  public function runningEmpty() {
    new Process('');
  }

  #[@test, @expect(IllegalStateException::class)]
  public function nonExistantProcessId() {
    Process::getProcessById(-1);
  }

  #[@test]
  public function getByProcessId() {
    $pid= getmypid();
    $p= Process::getProcessById($pid);
    $this->assertInstanceOf(Process::class, $p);
    $this->assertEquals($pid, $p->getProcessId());
  }

  #[@test]
  public function doubleClose() {
    $p= new Process($this->executable(), $this->arguments('-r', 'exit(222);'));
    $this->assertEquals(222, $p->close());
    $this->assertEquals(222, $p->close());
  }

  #[@test, @expect(class= IllegalStateException::class, withMessage= '/Cannot close not-owned/')]
  public function closingProcessByProcessId() {
    Process::getProcessById(getmypid())->close();
  }

  #[@test]
  public function hugeStdout() {
    $p= new Process($this->executable(), $this->arguments('-r', 'fputs(STDOUT, str_repeat("*", 65536));'));
    $out= '';
    while (!$p->out->eof()) {
      $out.= $p->out->read();
    }
    $p->close();
    $this->assertEquals(65536, strlen($out));
  }

  #[@test]
  public function hugeStderr() {
    $p= new Process($this->executable(), $this->arguments('-r', 'fputs(STDERR, str_repeat("*", 65536));'));
    $err= '';
    while (!$p->err->eof()) {
      $err.= $p->err->read();
    }
    $p->close();
    $this->assertEquals(65536, strlen($err));
  }

  #[@test]
  public function new_process_is_running() {
    $p= new Process($this->executable(), $this->arguments('-r', 'fgets(STDIN, 8192);'));
    $this->assertTrue($p->running());
    $p->in->writeLine();
    $p->close();
  }

  #[@test]
  public function process_is_not_running_after_it_exited() {
    $p= new Process($this->executable(), $this->arguments('-r', 'exit(0);'));
    $p->close();
    $this->assertFalse($p->running());
  }

  #[@test]
  public function runtime_is_running() {
    $p= Runtime::getInstance()->getExecutable();
    $this->assertTrue($p->running());
  }

  #[@test]
  public function mirror_is_not_running() {
    $p= new Process($this->executable(), $this->arguments('-r', 'fgets(STDIN, 8192); exit(0);'));
    $mirror= Process::getProcessById($p->getProcessId());
    $p->in->write("\n");
    $p->close();
    $this->assertFalse($mirror->running());
  }

  #[@test]
  public function terminate() {
    $p= new Process($this->executable(), $this->arguments('-r', 'sleep(10);'));
    $p->terminate();
    $this->assertNotEquals(0, $p->close());
  }

  #[@test]
  public function terminate_not_owned() {
    $p= new Process($this->executable(), $this->arguments('-r', 'sleep(10);'));
    Process::getProcessById($p->getProcessId())->terminate();
    $this->assertNotEquals(0, $p->close());
  }
}
