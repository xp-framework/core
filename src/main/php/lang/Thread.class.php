<?php namespace lang;

use util\Objects;

/**
 * Thread
 *
 * ```php
 * use lang\Thread;
 * 
 * class TimerThread extends Thread {
 *   public
 *     $ticks    = 0,
 *     $timeout  = 0;
 *     
 *   public function __construct($timeout) {
 *     $this->timeout= $timeout;
 *     parent::__construct('timer.'.$timeout);
 *   }
 *     
 *   public function run() {
 *     while ($this->ticks < $this->timeout) {
 *       Thread::sleep(1000);
 *       $this->ticks++;
 *       printf("<%s> tick\n", $this->name);
 *     }
 *     printf("<%s> time's up!\n", $this->name);
 *   }
 * }
 * 
 * $t[0]= new TimerThread(5);
 * $t[0]->start();
 * $t[1]= new TimerThread(2);
 * $t[1]->start();
 * 
 * for ($i= 0; $i < 3; $i++) {
 *   echo "<main> Waiting...\n";
 *   sleep(1);
 * }
 * 
 * $t[0]->join();
 * $t[1]->join();
 * ```
 *
 * @ext      pcntl
 * @ext      posix
 * @see      http://news.xp-framework.net/article/168/2007/04/05/
 * @see      xp://lang.Runnable
 */
class Thread {
  public
    $name     = '',
    $running  = false;
    
  protected
    $target   = null,
    $_id      = -1,
    $_pid     = -1;
    
  /**
   * Constructor
   *
   * Implementation by subclassing:
   * ```php
   * class ComputeThread extends Thread {
   *   public function run() {
   *     // ...
   *   }
   * }
   *
   * $thread= new ComputeThread('computr1');
   * $thread->start();
   * ```
   * 
   * Implementation by passing a Runnable: 
   * ```php
   * $thread= new Thread(newinstance('lang.Runnable', [], '{
   *   public function run() {
   *     // ...
   *   }
   * }'));
   * $thread->start();
   * ```
   *
   * @param   var arg default NULL
   */
  public function __construct($arg= null) {
    if ($arg instanceof Runnable) {
      $this->target= $arg;
      $this->name= nameof($arg);
    } else {
      $this->target= null;
      $this->name= $arg ? $arg : nameof($this);
    }
  }
  
  /**
   * Returns whether this thread is running
   *
   * @return  bool
   */
  public function isRunning() {
    return $this->running;
  }
  
  /**
   * Set Name
   *
   * @param   string name
   */
  public function setName($name) {
    $this->name= $name;
  }

  /**
   * Get Name
   *
   * @return  string
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * Get Target
   *
   * @return var (Runnable or Thread)
   */
  public function getTarget() {
    return (null === $this->target) ? $this : $this->target;
  }

  /**
   * Causes the currently executing thread to sleep (temporarily cease 
   * execution) for the specified number of milliseconds. 
   *
   * @param   int millis
   */
  public static function sleep($millis) {
    usleep($millis * 1000);
  }

  /**
   * Starts thread execution
   *
   * @throws  lang.IllegalThreadStateException if this thread is already running
   * @throws  lang.SystemException if the thread cannot be started
   */
  public function start() {
    if ($this->isRunning()) {
      throw new IllegalThreadStateException('Already running');
    }

    $parent= getmypid();
    $pid= pcntl_fork();
    if (-1 == $pid) {     // Cannot fork
      throw new SystemException('Cannot fork', 255);
    } else if ($pid) {     // Parent
      $this->running= true;
      $this->_id= $pid;
      $this->_pid= $parent;
    } else {              // Child
      $this->running= true;
      $this->_id= getmypid();
      $this->_pid= $parent;
      try {
        exit($this->getTarget()->run());
      } catch (SystemExit $e) {
        if ($message= $e->getMessage()) echo $message, "\n";
        exit($e->getCode());
      } catch (\Throwable $t) {
        fputs(STDERR, 'Uncaught exception (in child #'.$this->_id.'): '.$t->toString());
        exit(0xf0);
      }
    }
  }
  
  /**
   * Join this thread. The optional parameter wait may be set to FALSE to
   * return immediately if this thread hasn't terminated yet.
   *
   * @param   bool wait default TRUE
   * @return  int status
   * @see     php://pcntl_waitpid
   */
  public function join($wait= true) {
    if (!$this->isRunning()) {
      throw new IllegalThreadStateException('Cannot join no longer running thread.');
    }

    if (0 == pcntl_waitpid($this->_id, $status, $wait ? WUNTRACED : WNOHANG)) return -1;
    $this->running= false;
    return $status;
  }
  
  /**
   * Stop this thread
   *
   * @param   int signal default SIGINT
   * @throws  lang.IllegalThreadStateException
   */
  public function stop($signal= SIGINT) {
    if (!$this->isRunning()) {
      throw new IllegalThreadStateException('Cannot stop no longer running thread.');
    }
    posix_kill($this->_id, $signal);
    $this->running= false;
  }
  
  /**
   * Returns thread id of running or already stopped thread
   *
   * @return  int
   */
  public function getId() {
    return $this->_id;
  }
  
  /**
   * Returns thread's parent id
   *
   * @return  int
   */
  public function getParentId() {
    return $this->_pid;
  }
  
  /**
   * Creates a string representation
   *
   * @return  string
   */
  public function toString() {
    return sprintf('%s[%s%d]@%s', nameof($this), $this->isRunning() ? 'R' : 'S', $this->_id, Objects::stringOf($this));
  }
  
  /**
   * Subclasses of Thread should override this method.
   *
   * @return  var
   */
  public function run() { }
}
