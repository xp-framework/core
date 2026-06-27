<?php namespace io\streams;

use ReflectionFunction, php_user_filter;
use lang\IllegalArgumentException;

/** @see https://www.php.net/manual/en/filters.php */
abstract class FilterStream {
  public static $filters= [];
  private static $id= 0;
  protected $fd;
  private $remove= [];

  static function __static() {
    stream_filter_register('iostrl.*', get_class(new class() extends php_user_filter {
      public function filter($in, $out, &$consumed, bool $closing): int {
        while ($bucket= stream_bucket_make_writeable($in)) {
          $consumed+= $bucket->datalen;
          $bucket->data= FilterStream::$filters[$this->filtername]($bucket->data);
          null === $bucket->data || stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
      }
    }));
    stream_filter_register('iostrf.*', get_class(new class() extends php_user_filter {
      public function filter($in, $out, &$consumed, bool $closing): int {
        return FilterStream::$filters[$this->filtername]($in, $out, $consumed, $closing);
      }
    }));
  }

  /**
   * Appends a filter and returns its handle
   *
   * @param  string|callable $filter
   * @param  array $parameters
   * @return mixed
   * @throws lang.IllegalArgumentException
   */
  public function append($filter, $parameters= []) {
    if (is_string($filter)) {
      $name= $filter;
    } else {
      $f= new ReflectionFunction($filter);
      $this->remove[]= $name= (1 === $f->getNumberOfParameters() ? 'iostrl.' : 'iostrf.').(++self::$id);
      self::$filters[$name]= $filter;
    }

    if (!($handle= stream_filter_append($this->fd, $name, static::MODE, $parameters))) {
      throw new IllegalArgumentException('Could not append stream filter '.$name);
    }
    return $handle;
  }

  /**
   * Removes a stream filter
   *
   * @param  mixed $handle
   * @return void
   * @throws lang.IllegalArgumentException
   */
  public function remove($handle) {
    if (!stream_filter_remove($handle)) {
      throw new IllegalArgumentException('Could not remove stream filter '.$name);
    }
  }

  /**
   * Close stream
   *
   * @return void
   */
  public function close() {
    fclose($this->fd);
    $this->fd= null;
  }

  /** Ensures stream is closed */
  public function __destruct() {
    foreach ($this->remove as $filter) {
      unset(self::$filters[$filter]);
    }
    $this->fd && $this->close();
  }
}