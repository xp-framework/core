<?php namespace net\xp_framework\unittest\core;

use io\streams\{MemoryOutputStream, Streams};
use io\{IOException, TempFile};
use lang\{Environment, IllegalStateException, Process, Runtime};
use unittest\{AssertionFailedError, BeforeClass, Expect, PrerequisitesNotMetError, Test, Values, TestCase};

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
    if (strstr(php_uname('v'), 'Windows Server 2016')) {
      throw new PrerequisitesNotMetError('Process execution bug on Windows Server 2016', null, ['enabled']);
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
  public function stdin_stdout_roundtrip() {
    $p= new Process($this->executable(), ['-r', 'fprintf(STDOUT, fread(STDIN, 0xFF));']);
    $p->in->write('IN');
    $p->in->close();
    $out= $p->out->read();
    $p->close();
    $this->assertEquals('IN', $out);
  }

  #[Test, Values([[null], [[1 => ['pipe', 'w']]]])]
  public function reading_from_stdout($descriptors) {
    $p= new Process($this->executable(), ['-r', 'fprintf(STDOUT, "OUT");'], null, null, $descriptors);
    $out= $p->out->read();
    $p->close();
    $this->assertEquals('OUT', $out);
  }

  #[Test, Values([[null], [[2 => ['pipe', 'w']]]])]
  public function reading_from_stderr($descriptors) {
    $p= new Process($this->executable(), ['-r', 'fprintf(STDERR, "ERR");'], null, null, $descriptors);
    $err= $p->err->read();
    $p->close();
    $this->assertEquals('ERR', $err);
  }

  #[Test]
  public function stderr_redirected_to_stdout() {
    $p= new Process($this->executable(), ['-r', 'fprintf(STDERR, "ERR");'], null, null, [2 => ['redirect', 1]]);
    $out= $p->out->read();
    $p->close();
    $this->assertEquals('ERR', $out);
  }

  #[Test]
  public function stderr_redirected_to_null() {
    $p= new Process($this->executable(), ['-r', 'fprintf(STDERR, "ERR"); fprintf(STDOUT, "OK");'], null, null, [2 => ['null']]);
    $out= $p->out->read();
    $p->close();
    $this->assertEquals('OK', $out);
  }

  #[Test]
  public function stderr_redirected_to_file() {
    $err= new TempFile();

    $p= new Process($this->executable(), ['-r', 'fprintf(STDERR, "ERR");'], null, null, [2 => ['file', $err->getURI(), 'w']]);
    $p->close();

    try {
      $err->open(TempFile::READ);
      $this->assertEquals('ERR', $err->read(3));
    } finally {
      $err->close();
      $err->unlink();
    }
  }

  #[Test]
  public function stderr_redirected_to_file_handle() {
    $err= new TempFile();
    $err->open(TempFile::READWRITE);

    $p= new Process($this->executable(), ['-r', 'fprintf(STDERR, "ERR");'], null, null, [2 => $err->getHandle()]);
    $p->close();

    try {
      $err->seek(0, SEEK_SET);
      $this->assertEquals('ERR', $err->read(3));
    } finally {
      $err->close();
      $err->unlink();
    }
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