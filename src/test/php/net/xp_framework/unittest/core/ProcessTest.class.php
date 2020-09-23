<?php namespace net\xp_framework\unittest\core;

use io\IOException;
use io\streams\{MemoryOutputStream, Streams};
use lang\{Environment, IllegalStateException, Process, Runtime};
use unittest\{AssertionFailedError, BeforeClass, Expect, PrerequisitesNotMetError, Test, TestCase};

class ProcessTest extends TestCase {

  /**
   * Skips tests if process execution has been disabled.
   *
   * @return void
   */
  #[BeforeClass]
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
   * Test process status information methods
   *
   * @see      xp://lang.Process#getProcessId
   * @see      xp://lang.Process#getFilename
   * @see      xp://lang.Process#getCommandLine
   * @see      xp://lang.Process#exitValue
   */
  #[Test]
  public function information() {
    $p= new Process($this->executable(), ['-v']);
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

  #[Test]
  public function newInstance() {
    $p= Runtime::getInstance()->getExecutable()->newInstance(['-v']);
    $version= 'PHP '.PHP_VERSION;
    $this->assertEquals($version, $p->out->read(strlen($version)));
    $p->close();
  }

  #[Test]
  public function exitValueReturnedFromClose() {
    $p= new Process($this->executable(), ['-r', 'exit(0);']);
    $this->assertEquals(0, $p->close());
  }

  #[Test]
  public function nonZeroExitValueReturnedFromClose() {
    $p= new Process($this->executable(), ['-r', 'exit(2);']);
    $this->assertEquals(2, $p->close());
  }

  #[Test]
  public function exitValue() {
    $p= new Process($this->executable(), ['-r', 'exit(0);']);
    $p->close();
    $this->assertEquals(0, $p->exitValue());
  }

  #[Test]
  public function nonZeroExitValue() {
    $p= new Process($this->executable(), ['-r', 'exit(2);']);
    $p->close();
    $this->assertEquals(2, $p->exitValue());
  }

  #[Test]
  public function stdIn() {
    $p= new Process($this->executable(), ['-r', 'fprintf(STDOUT, fread(STDIN, 0xFF));']);
    $p->in->write('IN');
    $p->in->close();
    $out= $p->out->read();
    $p->close();
    $this->assertEquals('IN', $out);
  }

  #[Test]
  public function stdOut() {
    $p= new Process($this->executable(), ['-r', 'fprintf(STDOUT, "OUT");']);
    $out= $p->out->read();
    $p->close();
    $this->assertEquals('OUT', $out);
  }

  #[Test]
  public function stdErr() {
    $p= new Process($this->executable(), ['-r', 'fprintf(STDERR, "ERR");']);
    $err= $p->err->read();
    $p->close();
    $this->assertEquals('ERR', $err);
  }

  #[Test, Expect(IOException::class)]
  public function runningNonExistantFile() {
    new Process(':FILE_DOES_NOT_EXIST:');
  }

  #[Test, Expect(IOException::class)]
  public function runningDirectory() {
    new Process(Environment::tempDir());
  }

  #[Test, Expect(IOException::class)]
  public function runningEmpty() {
    new Process('');
  }

  #[Test, Expect(IllegalStateException::class)]
  public function nonExistantProcessId() {
    Process::getProcessById(-1);
  }

  #[Test]
  public function getByProcessId() {
    $pid= getmypid();
    $p= Process::getProcessById($pid);
    $this->assertInstanceOf(Process::class, $p);
    $this->assertEquals($pid, $p->getProcessId());
  }

  #[Test]
  public function doubleClose() {
    $p= new Process($this->executable(), ['-r', 'exit(222);']);
    $this->assertEquals(222, $p->close());
    $this->assertEquals(222, $p->close());
  }

  #[Test, Expect(['class' => IllegalStateException::class, 'withMessage' => '/Cannot close not-owned/'])]
  public function closingProcessByProcessId() {
    Process::getProcessById(getmypid())->close();
  }

  #[Test]
  public function hugeStdout() {
    $p= new Process($this->executable(), ['-r', 'fputs(STDOUT, str_repeat("*", 65536));']);
    $out= '';
    while (!$p->out->eof()) {
      $out.= $p->out->read();
    }
    $p->close();
    $this->assertEquals(65536, strlen($out));
  }

  #[Test]
  public function hugeStderr() {
    $p= new Process($this->executable(), ['-r', 'fputs(STDERR, str_repeat("*", 65536));']);
    $err= '';
    while (!$p->err->eof()) {
      $err.= $p->err->read();
    }
    $p->close();
    $this->assertEquals(65536, strlen($err));
  }

  #[Test]
  public function new_process_is_running() {
    $p= new Process($this->executable(), ['-r', 'fgets(STDIN, 8192);']);
    $this->assertTrue($p->running());
    $p->in->writeLine();
    $p->close();
  }

  #[Test]
  public function process_is_not_running_after_it_exited() {
    $p= new Process($this->executable(), ['-r', 'exit(0);']);
    $p->close();
    $this->assertFalse($p->running());
  }

  #[Test]
  public function runtime_is_running() {
    $p= Runtime::getInstance()->getExecutable();
    $this->assertTrue($p->running());
  }

  #[Test]
  public function mirror_is_not_running() {
    $p= new Process($this->executable(), ['-r', 'fgets(STDIN, 8192); exit(0);']);
    $mirror= Process::getProcessById($p->getProcessId());
    $p->in->write("\n");
    $p->close();
    $this->assertFalse($mirror->running());
  }

  #[Test]
  public function terminate() {
    $p= new Process($this->executable(), ['-r', 'sleep(10);']);
    $p->terminate();
    $this->assertNotEquals(0, $p->close());
  }

  #[Test]
  public function terminate_not_owned() {
    $p= new Process($this->executable(), ['-r', 'sleep(10);']);
    Process::getProcessById($p->getProcessId())->terminate();
    $this->assertNotEquals(0, $p->close());
  }
}