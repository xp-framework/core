<?php namespace net\xp_framework\unittest\core;

use io\IOException;
use lang\Process;
use unittest\actions\IsPlatform;
use unittest\{Action, Expect, TestCase, Test};

/**
 * TestCase for lang.Process' resolve() method
 *
 * @see      xp://lang.Process
 */
class ProcessResolveTest extends TestCase {
  private $origDir;

  /** @return void */
  public function setUp() {
    $this->origDir= getcwd();
  }
  
  /** @return void */
  public function tearDown() {
    chdir($this->origDir);
  }

  /**
   * Replaces backslashes in the specified path by the new separator. If $skipDrive is set
   * to TRUE, the leading drive letter definition (e.g. 'C:') is removed from the new path.
   *
   * @param  string $path
   * @param  string $newSeparator
   * @param  bool $skipDrive
   * @return string
   */
  private function replaceBackslashSeparator($path, $newSeparator, $skipDrive) {
    $parts= explode('\\', $path);
    if (preg_match('/[a-z]:/i', $parts[0]) != 0 && $skipDrive) array_shift($parts);
    return implode($newSeparator, $parts);
  }

  #[Test, Action(eval: 'new IsPlatform("WIN")')]
  public function resolveFullyQualifiedWithDriverLetter() {
    $this->assertTrue(is_executable(Process::resolve(getenv('WINDIR').'\\EXPLORER.EXE')));
  }

  #[Test, Action(eval: 'new IsPlatform("WIN")')]
  public function resolveFullyQualifiedWithDriverLetterWithoutExtension() {
    $this->assertTrue(is_executable(Process::resolve(getenv('WINDIR').'\\EXPLORER')));
  }

  #[Test, Action(eval: 'new IsPlatform("WIN")')]
  public function resolveFullyQualifiedWithBackSlash() {
    $path= '\\'.$this->replaceBackslashSeparator(getenv('WINDIR').'\\EXPLORER.EXE', '\\', TRUE);
    chdir('C:');
    $this->assertTrue(is_executable(Process::resolve($path)));
  }

  #[Test, Action(eval: 'new IsPlatform("WIN")')]
  public function resolveFullyQualifiedWithSlash() {
    $path= '/'.$this->replaceBackslashSeparator(getenv('WINDIR').'\\EXPLORER.EXE', '/', TRUE);
    chdir('C:');
    $this->assertTrue(is_executable(Process::resolve($path)));
  }

  #[Test, Action(eval: 'new IsPlatform("WIN")')]
  public function resolveFullyQualifiedWithoutExtension() {
    $path='\\'.$this->replaceBackslashSeparator(getenv('WINDIR').'\\EXPLORER', '\\', TRUE);
    chdir('C:');
    $this->assertTrue(is_executable(Process::resolve($path)));
  }

  #[Test, Action(eval: 'new IsPlatform("WIN")')]
  public function resolveCommandInPath() {
    $this->assertTrue(is_executable(Process::resolve('explorer.exe')));
  }

  #[Test, Action(eval: 'new IsPlatform("WIN")')]
  public function resolveCommandInPathWithoutExtension() {
    $this->assertTrue(is_executable(Process::resolve('explorer')));
  }

  #[Test, Expect(IOException::class)]
  public function resolveSlashDirectory() {
    Process::resolve('/');
  }

  #[Test, Action(eval: 'new IsPlatform("WIN")'), Expect(IOException::class)]
  public function resolveBackslashDirectory() {
    Process::resolve('\\');
  }

  #[Test, Expect(IOException::class)]
  public function resolveEmpty() {
    Process::resolve('');
  }

  #[Test, Expect(IOException::class)]
  public function resolveNonExistant() {
    Process::resolve('@@non-existant@@');
  }

  #[Test, Expect(IOException::class)]
  public function resolveNonExistantFullyQualified() {
    Process::resolve('/@@non-existant@@');
  }

  #[Test, Action(eval: 'new IsPlatform("ANDROID")')]
  public function resolveFullyQualifiedOnAndroid() {
    $fq= getenv('ANDROID_ROOT').'/framework/core.jar';
    $this->assertEquals($fq, Process::resolve($fq));
  }

  #[Test, Action(eval: 'new IsPlatform("!(WIN|ANDROID)")')]
  public function resolveFullyQualifiedOnPosix() {
    $this->assertTrue(in_array(Process::resolve('/bin/ls'), ['/usr/bin/ls', '/bin/ls']));
  }

  #[Test, Values(['"ls"', "'ls'"]), Action(eval: 'new IsPlatform("!(WIN|ANDROID)")')]
  public function resolveQuotedOnPosix($command) {
    $this->assertTrue(in_array(Process::resolve($command), ['/usr/bin/ls', '/bin/ls']));
  }

  #[Test, Action(eval: 'new IsPlatform("WIN")')]
  public function resolveQuotedOnWindows() {
    $this->assertTrue(is_executable(Process::resolve('"explorer"')));
  }

  #[Test, Action(eval: 'new IsPlatform("!(WIN|ANDROID)")')]
  public function resolve() {
    $this->assertTrue(in_array(Process::resolve('ls'), ['/usr/bin/ls', '/bin/ls']));
  }
}