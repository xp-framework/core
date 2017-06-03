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
   * $str= FileUtil::getContents(new File('/etc/passwd'));
   * ```
   *
   * @param   io.File $file
   * @return  string file contents
   * @throws  io.IOException
   * @throws  io.FileNotFoundException
   */
  public static function getContents($file) {
    if ($file->isOpen()) {
      $data= '';
      do {
        $data.= $file->read();
      } while (!$file->eof());
    } else {
      clearstatcache();
      $file->open(File::READ);
      $size= $file->size();

      // Read until EOF. Best case scenario is that this will run exactly once.
      $data= '';
      do {
        $l= $size - strlen($data);
        $data.= $file->read($l);
      } while ($l > 0 && !$file->eof());
      $file->close();
    }
    return $data;
  }
  
  /**
   * Set file contents. If the file was previously open, it is not closed
   * after the data has been written.
   *
   * ```php
   * $bytes_written= FileUtil::setContents(new File('myfile'), 'Hello world');
   * ```
   *
   * @param   io.File $file
   * @param   string $data
   * @return  int
   * @throws  io.IOException
   */
  public static function setContents($file, $data) {
    if ($file->isOpen()) {
      return $file->write($data);
    } else {
      $file->open(File::WRITE);
      $written= $file->write($data);
      $file->close();
      return $written;
    }
  }
}
