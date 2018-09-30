<?php namespace io;

/**
 * File utility functions
 *
 * @see   xp://io.File
 * @test  xp://net.xp_framework.unittest.io.FileUtilTest
 */
class FileUtil {

  /**
   * Retrieve file contents as a string. If the file was previously open,
   * it is not closed when EOF is reached.
   *
   * ```php
   * $str= FileUtil::read(new File('/etc/passwd'));
   * ```
   *
   * @param  io.File $file
   * @return string file contents
   * @throws io.IOException
   * @throws io.FileNotFoundException
   */
  public static function read($file) {
    if ($file->isOpen()) {
      $bytes= '';
      do {
        $bytes.= $file->read();
      } while (!$file->eof());
    } else {
      clearstatcache();
      $file->open(File::READ);
      $size= $file->size();

      // Read until EOF. Best case scenario is that this will run exactly once.
      $bytes= '';
      do {
        $l= $size - strlen($bytes);
        $bytes.= $file->read($l);
      } while ($l > 0 && !$file->eof());
      $file->close();
    }
    return $bytes;
  }

  /**
   * Set file contents. If the file was previously open, it is not closed
   * after the bytes have been written.
   *
   * ```php
   * $written= FileUtil::write(new File('myfile'), 'Hello world');
   * ```
   *
   * @param  io.File $file
   * @param  string $bytes
   * @return int
   * @throws io.IOException
   */
  public static function write($file, $bytes) {
    if ($file->isOpen()) {
      $file->seek(0, SEEK_SET);
      $written= $file->write($bytes);
      $file->truncate($written);
    } else {
      $file->open(File::WRITE);
      $written= $file->write($bytes);
      $file->close();
    }
    return $written;
  }

  /**
   * Append file contents. If the file was previously open, it is not closed
   * after the bytes have been written.
   *
   * @param  io.File $file
   * @param  string $bytes
   * @return int
   * @throws io.IOException
   */
  public static function append($file, $bytes) {
    if ($file->isOpen()) {
      $written= $file->write($bytes);
    } else {
      $file->open(File::APPEND);
      $written= $file->write($bytes);
      $file->close();
    }
    return $written;
  }

  /** @deprecated */
  public static function getContents($file) { return self::read($file); }

  /** @deprecated */
  public static function setContents($file, $bytes) { return self::write($file, $bytes); }
}
