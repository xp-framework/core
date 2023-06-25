<?php namespace lang\unittest;

use lang\CommandLine;
use unittest\{Assert, Test};

class CommandLineTest {

  #[Test]
  public function forWindows() {
    Assert::equals(CommandLine::$WINDOWS, CommandLine::forName('Windows'));
  }

  #[Test]
  public function forWinNT() {
    Assert::equals(CommandLine::$WINDOWS, CommandLine::forName('WINNT'));
  }

  #[Test]
  public function forBSD() {
    Assert::equals(CommandLine::$UNIX, CommandLine::forName('FreeBSD'));
  }

  #[Test]
  public function forLinux() {
    Assert::equals(CommandLine::$UNIX, CommandLine::forName('Linux'));
  }

  #[Test]
  public function noquotingWindows() {
    Assert::equals('php -v', CommandLine::$WINDOWS->compose('php', ['-v']));
  }

  #[Test]
  public function noquotingUnix() {
    Assert::equals('php -v', CommandLine::$UNIX->compose('php', ['-v']));
  }

  #[Test]
  public function emptyArgumentQuotingWindows() {
    Assert::equals('echo "" World', CommandLine::$WINDOWS->compose('echo', ['', 'World']));
  }

  #[Test]
  public function emptyArgumentQuotingUnix() {
    Assert::equals("echo '' World", CommandLine::$UNIX->compose('echo', ['', 'World']));
  }

  #[Test]
  public function commandIsQuotedWindows() {
    Assert::equals(
      '"C:/Users/Timm Friebe/php" -v', 
      CommandLine::$WINDOWS->compose('C:/Users/Timm Friebe/php', ['-v'])
    );
  }

  #[Test]
  public function commandIsQuotedUnix() {
    Assert::equals(
      "'/Users/Timm Friebe/php' -v", 
      CommandLine::$UNIX->compose('/Users/Timm Friebe/php', ['-v'])
    );
  }

  #[Test]
  public function argumentsContainingSpacesAreQuotedWindows() {
    Assert::equals(
      'php -r "a b"',
      CommandLine::$WINDOWS->compose('php', ['-r', 'a b'])
    );
  }

  #[Test]
  public function argumentsContainingSpacesAreQuotedUnix() {
    Assert::equals(
      "php -r 'a b'",
      CommandLine::$UNIX->compose('php', ['-r', 'a b'])
    );
  }

  #[Test]
  public function quotesInArgumentsAreEscapedWindows() {
    Assert::equals(
      'php -r "a"""b"',
      CommandLine::$WINDOWS->compose('php', ['-r', 'a"b'])
    );
  }

  #[Test]
  public function quotesInArgumentsAreEscapedUnix() {
    Assert::equals(
      "php -r 'a'\''b'",
      CommandLine::$UNIX->compose('php', ['-r', "a'b"])
    );
  }
  
  #[Test]
  public function emptyArgsWindows() {
    Assert::equals(
      ['C:\\Windows\\Explorer.EXE'],
      CommandLine::$WINDOWS->parse('C:\\Windows\\Explorer.EXE')
    );
  }

  #[Test]
  public function emptyArgsUnix() {
    Assert::equals(
      ['/etc/init.d/apache'],
      CommandLine::$UNIX->parse('/etc/init.d/apache')
    );
  }

  #[Test]
  public function guidArgWindows() {
    Assert::equals(
      ['taskeng.exe', '{58B7C886-2D94-4DBF-BBB9-96608B332124}'],
      CommandLine::$WINDOWS->parse('taskeng.exe {58B7C886-2D94-4DBF-BBB9-96608B332124}')
    );
  }

  #[Test]
  public function guidArgUnix() {
    Assert::equals(
      ['guid', '{58B7C886-2D94-4DBF-BBB9-96608B332124}'],
      CommandLine::$WINDOWS->parse('guid {58B7C886-2D94-4DBF-BBB9-96608B332124}')
    );
  }

  #[Test]
  public function quotedCommandWindows() {
    Assert::equals(
      ['C:\\Program Files\\Windows Sidebar\\sidebar.exe', '/autoRun'],
      CommandLine::$WINDOWS->parse('"C:\\Program Files\\Windows Sidebar\\sidebar.exe" /autoRun')
    );
  }

  #[Test]
  public function quotedCommandUnix() {
    Assert::equals(
      ['/opt/MySQL Daemon/bin/mysqld', '--pid-file=/var/mysql.pid'],
      CommandLine::$UNIX->parse("'/opt/MySQL Daemon/bin/mysqld' --pid-file=/var/mysql.pid")
    );
  }

  #[Test]
  public function doubleQuotedCommandUnix() {
    Assert::equals(
      ['/opt/MySQL Daemon/bin/mysqld', '--pid-file=/var/mysql.pid'],
      CommandLine::$UNIX->parse('"/opt/MySQL Daemon/bin/mysqld" --pid-file=/var/mysql.pid')
    );
  }

  #[Test]
  public function quotedArgumentPartWindows() {
    Assert::equals(
      ['C:/usr/bin/php', '-q', '-dinclude_path=.:/usr/share', '-dauto_globals_jit=0'],
      CommandLine::$WINDOWS->parse('C:/usr/bin/php -q -dinclude_path=".:/usr/share" -dauto_globals_jit=0')
    );        
  }

  #[Test]
  public function quotedArgumentPartUnix() {
    Assert::equals(
      ['/usr/bin/php', '-q', '-dinclude_path=".:/usr/share"', '-dauto_globals_jit=0'],
      CommandLine::$UNIX->parse('/usr/bin/php -q -dinclude_path=".:/usr/share" -dauto_globals_jit=0')
    );        
  }

  #[Test]
  public function quotedCommandAndArgumentPartWindows() {
    Assert::equals(
      ['C:/usr/bin/php', '-q', '-dinclude_path=.:/usr/share', '-dauto_globals_jit=0'],
      CommandLine::$WINDOWS->parse('"C:/usr/bin/php" -q -dinclude_path=".:/usr/share" -dauto_globals_jit=0')
    );
  }

  #[Test]
  public function quotedCommandAndArgumentPartUnix() {
    Assert::equals(
      ['/usr/bin/php', '-q', '-dinclude_path=".:/usr/share"', '-dauto_globals_jit=0'],
      CommandLine::$UNIX->parse('"/usr/bin/php" -q -dinclude_path=".:/usr/share" -dauto_globals_jit=0')
    );
  }

  #[Test]
  public function quotedArgumentWindows() {
    Assert::equals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt'],
      CommandLine::$WINDOWS->parse('sublimetext "/mnt/c/Users/Mr. Example/notes.txt"')
    );
  }

  #[Test]
  public function doubleQuotedArgumentUnix() {
    Assert::equals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt'],
      CommandLine::$UNIX->parse("sublimetext '/mnt/c/Users/Mr. Example/notes.txt'")
    );
  }

  #[Test]
  public function quotedArgumentUnix() {
    Assert::equals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt'],
      CommandLine::$UNIX->parse('sublimetext "/mnt/c/Users/Mr. Example/notes.txt"')
    );
  }

  #[Test]
  public function quotedArgumentsWindows() {
    Assert::equals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt', '../All Notes.txt'],
      CommandLine::$WINDOWS->parse('sublimetext "/mnt/c/Users/Mr. Example/notes.txt" "../All Notes.txt"')
    );
  }

  #[Test]
  public function quotedArgumentsUnix() {
    Assert::equals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt', '../All Notes.txt'],
      CommandLine::$UNIX->parse("sublimetext '/mnt/c/Users/Mr. Example/notes.txt' '../All Notes.txt'")
    );
  }

  #[Test]
  public function doubleQuotedArgumentsUnix() {
    Assert::equals(
      ['sublimetext', '/mnt/c/Users/Mr. Example/notes.txt', '../All Notes.txt'],
      CommandLine::$UNIX->parse('sublimetext "/mnt/c/Users/Mr. Example/notes.txt" "../All Notes.txt"')
    );
  }

  #[Test]
  public function evalCommandLineWindows() {
    $cmd= 'xp xp.runtime.Evaluate "echo """Hello World""";"';
    Assert::equals(
      ['xp', 'xp.runtime.Evaluate', 'echo "Hello World";'],
      CommandLine::$WINDOWS->parse($cmd)
    );
  }

  #[Test]
  public function evalCommandLineWindowsUnclosed() {
    $cmd= 'xp xp.runtime.Evaluate "1+ 2';
    Assert::equals(
      ['xp', 'xp.runtime.Evaluate', '1+ 2'],
      CommandLine::$WINDOWS->parse($cmd)
    );
  }

  #[Test]
  public function evalCommandLineWindowsUnclosedTriple() {
    $cmd= 'xp xp.runtime.Evaluate "echo """Hello World';
    Assert::equals(
      ['xp', 'xp.runtime.Evaluate', 'echo "Hello World'],
      CommandLine::$WINDOWS->parse($cmd)
    );
  }

  #[Test]
  public function evalCommandLineWindowsTripleClosedBySingle() {
    $cmd= 'xp xp.runtime.Evaluate "echo """Hello World" a';
    Assert::equals(
      ['xp', 'xp.runtime.Evaluate', 'echo "Hello World', 'a'],
      CommandLine::$WINDOWS->parse($cmd)
    );
  }
}