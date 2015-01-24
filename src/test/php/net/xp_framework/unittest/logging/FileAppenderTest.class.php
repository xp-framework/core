<?php namespace net\xp_framework\unittest\logging;

use util\log\FileAppender;
use io\streams\Streams;
use io\streams\MemoryOutputStream;
use util\log\layout\PatternLayout;

/**
 * TestCase for FileAppender
 *
 * @see   xp://util.log.FileAppender
 */
class FileAppenderTest extends AppenderTest {

  /**
   * Defines stream wrapper
   */
  #[@beforeClass]
  public static function defineStreamWrapper() {
    $sw= \lang\ClassLoader::defineClass('FileAppender_StreamWrapper', 'lang.Object', [], '{
      public static $buffer= [];
      private static $meta= array(STREAM_META_ACCESS => 0666);
      private $handle;
      public $context;

      public function stream_open($path, $mode, $options, $opened_path) {
        if (strstr($mode, "r")) {
          if (!isset(self::$buffer[$path])) return false;
          self::$buffer[$path][0]= $mode;
          self::$buffer[$path][1]= 0;
        } else if (strstr($mode, "w")) {
          self::$buffer[$path]= array($mode, 0, "", self::$meta);
        } else if (strstr($mode, "a")) {
          if (!isset(self::$buffer[$path])) {
            self::$buffer[$path]= array($mode, 0, "", self::$meta);
          } else {
            self::$buffer[$path][0]= $mode;
          }
        }
        $this->handle= &self::$buffer[$path];
        return true;
      }

      public function stream_write($data) {
        $this->handle[1]+= strlen($data);
        $this->handle[2].= $data;
        return strlen($data);
      }

      public function stream_read($count) {
        $chunk= substr($this->handle[2], $this->handle[1], $count);
        $this->handle[1]+= strlen($chunk);
        $this->handle[2]= substr($this->handle[2], $this->handle[1]);
        return $chunk;
      }

      public function stream_flush() {
        return true;
      }

      public function stream_seek($offset, $whence) {
        if (SEEK_SET === $whence) {
          $this->handle[1]= $offset;
        } else if (SEEK_END === $whence) {
          $this->handle[1]= strlen($this->handle[2]);
        } else if (SEEK_CUR === $whence) {
          $this->handle[1]+= $offset;
        }
        return 0;   // Success
      }

      public function stream_eof() {
        return $this->handle[1] >= strlen($this->handle[2]);
      }

      public function stream_stat() {
        return array("size" => $this->handle[1]);
      }

      public function stream_close() {
        return true;
      }

      public function stream_metadata($path, $option, $value) {
        if (!isset(self::$buffer[$path])) return false;
        self::$buffer[$path][3][$option]= $value;
        return true;
      }

      public function url_stat($path) {
        if (!isset(self::$buffer[$path])) return false;
        return array(
          "size" => strlen(self::$buffer[$path][2]),
          "mode" => self::$buffer[$path][3][STREAM_META_ACCESS]
        );
      }
    }');
    stream_wrapper_register('test', $sw->literal());
  }

  /**
   * Creates new appender fixture
   *
   * @return  util.log.BufferedAppender
   */
  protected function newFixture() {
    return (new FileAppender('test://'.$this->name))->withLayout(new PatternLayout("[%l] %m\n"));
  }

  #[@test]
  public function append_one_message() {
    $fixture= $this->newFixture();
    $fixture->append($this->newEvent(\util\log\LogLevel::WARN, 'Test'));
    $this->assertEquals(
      "[warn] Test\n",
      file_get_contents($fixture->filename)
    );
  }

  #[@test]
  public function append_two_messages() {
    $fixture= $this->newFixture();
    $fixture->append($this->newEvent(\util\log\LogLevel::WARN, 'Test'));
    $fixture->append($this->newEvent(\util\log\LogLevel::INFO, 'Just testing'));
    $this->assertEquals(
      "[warn] Test\n[info] Just testing\n",
      file_get_contents($fixture->filename)
    );
  }

  #[@test]
  public function chmod_called_when_perms_given() {
    $fixture= $this->newFixture();
    $fixture->perms= '0640';  // -rw-r-----
    $fixture->append($this->newEvent(\util\log\LogLevel::WARN, 'Test'));
    $this->assertEquals(0640, fileperms($fixture->filename));
  }

  #[@test]
  public function chmod_not_called_without_initializing_perms() {
    $fixture= $this->newFixture();
    $fixture->append($this->newEvent(\util\log\LogLevel::WARN, 'Test'));
    $this->assertEquals(0666, fileperms($fixture->filename));
  }

  #[@test]
  public function filename_syncs_with_time() {
    $fixture= newinstance('util.log.FileAppender', array('test://fn%H'), '{
      protected $hour= 0;
      public function filename($ref= null) {
        return parent::filename(0 + 3600 * $this->hour++);
      }
    }');
    $fixture->setLayout(new PatternLayout("[%l] %m\n"));

    $fixture->append($this->newEvent(\util\log\LogLevel::INFO, 'One'));
    $fixture->append($this->newEvent(\util\log\LogLevel::INFO, 'Two'));

    $this->assertEquals(
      array('fn1' => true, 'fn2' => true, 'fn3' => false),
      array('fn1' => file_exists('test://fn01'), 'fn2' => file_exists('test://fn02'), 'fn3' => file_exists('test://fn03'))
    );
  }

  #[@test]
  public function filename_does_not_sync_with_time() {
    $fixture= $this->newFixture();
    $fixture->filename= 'test://file-%H:%M:%I:%S';
    $fixture->syncDate= false;

    $fixture->filename();
    $this->assertFalse(strpos($fixture->filename, '%'));
  }
}
