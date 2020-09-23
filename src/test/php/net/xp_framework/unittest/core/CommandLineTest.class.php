<?php namespace net\xp_framework\unittest\core;

use lang\CommandLine;
use unittest\Test;

/**
 * TestCase
 *
 * @see      xp://lang.CommandLine
 */
class CommandLineTest extends \unittest\TestCase {

  #[Test]
  public function forWindows() {
    $this->assertEquals(CommandLine::$WINDOWS, CommandLine::forName('Windows'));
  }

  #[Test]
  public function forWinNT() {
    $this->assertEquals(CommandLine::$WINDOWS, CommandLine::forName('WINNT'));
  }

  #[Test]
  public function forBSD() {
    $this->assertEquals(CommandLine::$UNIX, CommandLine::forName('FreeBSD'));
  }

  #[Test]
  public function forLinux() {
    $this->assertEquals(CommandLine::$UNIX, CommandLine::forName('Linux'));
  }

  #[Test]
  public function noquotingWindows() {
    $this->assertEquals('php -v', CommandLine::$WINDOWS->compose('php', ['-v']));
  }

  #[Test]
  public function noquotingUnix() {
    $this->assertEquals('php -v', CommandLine::$UNIX->compose('php', ['-v']));
  }

  #[Test]
  public function emptyArgumentQuotingWindows() {
    $this->assertEquals('echo "" World', CommandLine::$WINDOWS->compose('echo', ['', 'World']));
  }

  #[Test]
  public function emptyArgumentQuotingUnix() {
    $this->assertEquals("echo '' World", CommandLine::$UNIX->compose('echo', ['', 'World']));
  }

  #[Test]
  public function commandIsQuotedWindows() {
    $this->assertEquals(
      '"C:/Users/Timm Friebe/php" -v', 
      CommandLine::$WINDOWS->compose('C:/Users/Timm Friebe/php', ['-v'])
    );
  }

  #[Test]
  public function commandIsQuotedUnix() {
    $this->assertEquals(
      "'/Users/Timm Friebe/php' -v", 
      CommandLine::$UNIX->compose('/Users/Timm Friebe/php', ['-v'])
    );
  }

  #[Test]
  public function argumentsContainingSpacesAreQuotedWindows() {
    $this->assertEquals(
      'php -r "a b"',
      CommandLine::$WINDOWS->compose('php', ['-r', 'a b'])
    );
  }

  #[Test]
  public function argumentsContainingSpacesAreQuotedUnix() {
    $this->assertEquals(
      "php -r 'a b'",
      CommandLine::$UNIX->compose('php', ['-r', 'a b'])
    );
  }

  #[Test]
  public function quotesInArgumentsAreEscapedWindows() {
    $this->assertEquals(
      'php -r "a"""b"',
      CommandLine::$WINDOWS->compose('php', ['-r', 'a"b'])
    );
  }

  #[Test]
  public function quotesInArgumentsAreEscapedUnix() {
    $this->assertEquals(
      "php -r 'a'\''b'",
      CommandLine::$UNIX->compose('php', ['-r', "a'b"])
    );
  }
  
  #[Test]
  public function emptyArgsWindows() {
    $this->assertEquals(
      ['C:\\Windows\\Explorer.EXE'],
      CommandLine::$WINDOWS->parse('C:\\Windows\\Explorer.EXE')
    );
  }

  #[Test]
  public function emptyArgsUnix() {
    $this->assertEquals(
      ['/etc/init.d/apache'],
      CommandLine::$UNIX->parse('/etc/init.d/apache')
    );
  }

  #[Test]
  public function guidArgWindows() {
    $this->assertEquals(
      ['taskeng.exe', '{58B7C886-2D94-4DBF-BBB9-96608B332124}'],
      CommandLine::$WINDOWS->parse('taskeng.exe {58B7C886-2D94-4DBF-BBB9-96608B332124}')
    );
  }

  #[Test]
  public function guidArgUnix() {
    $this->assertEquals(
      ['guid', '{58B7C886-2D94-4DBF-BBB9-96608B332124}'],
      CommandLine::$WINDOWS->parse('guid {58B7C886-2D94-4DBF-BBB9-96608B332124}')
    );
  }

  #[Test]
  public function quotedCommandWindows() {
    $this->assertEquals(
      ['C:\\Program Files\\Windows Sidebar\\sidebar.exe', '/autoRun'],
      CommandLine::$WINDOWS->parse('"C:\\Program Files\\Windows Sidebar\\sidebar.exe" /autoRun')
    );
  }

  #[Test]
  public function quotedCommandUnix() {
    $this->assertEquals(
      ['/opt/MySQL Daemon/bin/mysqld', '--pid-file=/var/mysql.pid'],
      CommandLine::$UNIX->parse("'/opt/MySQL Daemon/bin/mysqld' --pid-file=/var/mysql.pid")
    );
  }

  #[Test]
  public function doubleQuotedCommandUnix() {
    $this->assertEquals(
      ['/opt/MySQL Daemon/bin/mysqld', '--pid-file=/var/mysql.pid'],
      CommandLine::$UNIX->parse('"/opt/MySQL Daemon/bin/mysqld" --pid-file=/var/mysql.pid')
    );
  }

  #[Test]
  public function quotedArgumentPartWindows() {
    $this->assertEquals(
      ['C:/usr/bin/php', '-q', '-dinclude_path=.:/usr/share', '-dauto_globals_jit=0'],
      CommandLine::$WINDOWS->parse('C:/usr/bin/php -q -dinclude_path=".:/usr/share" -dauto_globals_jit=0')
    );        
  }

  #[Test]
  public function quotedArgumentPartUnix() {
    $this->assertEquals(
      ['/usr/bin/php', '-q', '-dinclude_path=".:/usr/share"', '-dauto_globals_jit=0'],
      CommandLine::$UNIX->parse('/usr/bin/php -q -dinclude_path=".:/usr/share" -dauto_globals_jit=0')
    );        
  }

  #[Test]
  public function quotedCommandAndArgumentPartWindows() {
    $this->assertEquals(
      ['C:/usr/bin/php', '-q', '-dinclude_path=.:/usr/share', '-dauto_globals_jit=0'],
      CommandLine::$WINDOWS->parse('"C:/usr/bin/php" -q -dinclude_path=".:/usr/share" -dauto_globals_jit=0')
    );
  }

  #[Test]
  public function quotedCommandAndArgumentPartUnix() {
    $this->assertEquals(
      ['/usr/bin/php', '-q', '-dinclude_path=".:/usr/share"', '-dauto_globals_jit=0'],
      CommandLine::$UNIX->parse('"/usr/bin/php" -q -dinclude_path=".:/usr/share" -dauto_globals_jit=0')
    );
  }

  #[Test]
  public function quotedArgumentWindows() {
    $this->assertEquals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt'],
      CommandLine::$WINDOWS->parse('sublimetext "/mnt/c/Users/Mr. Example/notes.txt"')
    );
  }

  #[Test]
  public function doubleQuotedArgumentUnix() {
    $this->assertEquals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt'],
      CommandLine::$UNIX->parse("sublimetext '/mnt/c/Users/Mr. Example/notes.txt'")
    );
  }

  #[Test]
  public function quotedArgumentUnix() {
    $this->assertEquals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt'],
      CommandLine::$UNIX->parse('sublimetext "/mnt/c/Users/Mr. Example/notes.txt"')
    );
  }

  #[Test]
  public function quotedArgumentsWindows() {
    $this->assertEquals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt', '../All Notes.txt'],
      CommandLine::$WINDOWS->parse('sublimetext "/mnt/c/Users/Mr. Example/notes.txt" "../All Notes.txt"')
    );
  }

  #[Test]
  public function quotedArgumentsUnix() {
    $this->assertEquals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt', '../All Notes.txt'],
      CommandLine::$UNIX->parse("sublimetext '/mnt/c/Users/Mr. Example/notes.txt' '../All Notes.txt'")
    );
  }

  #[Test]
  public function doubleQuotedArgumentsUnix() {
    $this->assertEquals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt', '../All Notes.txt'],
      CommandLine::$UNIX->parse('sublimetext "/mnt/c/Users/Mr. Example/notes.txt" "../All Notes.txt"')
    );
  }

  #[Test]
  public function evalCommandLineWindows() {
    $cmd= 'xp xp.runtime.Evaluate "echo """Hello World""";"';
    $this->assertEquals(
      ['xp', 'xp.runtime.Evaluate', 'echo "Hello World";'],
      CommandLine::$WINDOWS->parse($cmd)
    );
  }

  #[Test]
  public function evalCommandLineWindowsUnclosed() {
    $cmd= 'xp xp.runtime.Evaluate "1+ 2';
    $this->assertEquals(
      ['xp', 'xp.runtime.Evaluate', '1+ 2'],
      CommandLine::$WINDOWS->parse($cmd)
    );
  }

  #[Test]
  public function evalCommandLineWindowsUnclosedTriple() {
    $cmd= 'xp xp.runtime.Evaluate "echo """Hello World';
    $this->assertEquals(
      ['xp', 'xp.runtime.Evaluate', 'echo "Hello World'],
      CommandLine::$WINDOWS->parse($cmd)
    );
  }

  #[Test]
  public function evalCommandLineWindowsTripleClosedBySingle() {
    $cmd= 'xp xp.runtime.Evaluate "echo """Hello World" a';
    $this->assertEquals(
      ['xp', 'xp.runtime.Evaluate', 'echo "Hello World', 'a'],
      CommandLine::$WINDOWS->parse($cmd)
    );
  }
}