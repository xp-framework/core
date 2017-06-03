<?php namespace io\sys;

use io\IOException;

define('IPC_QUEUE_PERM',  0666);
define('IPC_MSG_MAXSIZE', 16384);

/**
 * Send System V IPC messages
 * <quote>
 * Inter process messaging (IPC) is a great way to deal with communicating
 * threads. If you have forked processes, this could be a great way 
 * of passing out work to them.
 * </quote>
 *
 * Usage example [with threads]
 *
 * ```php
 * use io\sys\IPCQueue;
 * use io\sys\Ftok;
 * use lang\Thread;
 *
 * class SenderThread extends Thread {
 *   private $queue, $num;
 *
 *   public function __construct($num) {
 *     $this->num= $num;
 *     $this->queue= new IPCQueue(8925638);
 *     parent::__construct('sender.'.$this->num);
 *   }
 *
 *   public function run() {
 *     while ($this->sent < $this->num) {
 *       Thread::sleep(1000);
 *       $this->queue->putMessage(new IPCMessage('hello world'));
 *       $this->sent++;     
 *
 *     }
 *     Console::writeLinef(
 *       "<%s> sent %d messages\n",
 *       $this->name, $this->num
 *     );
 *   }
 * }
 *
 * class ReceiverThread extends Thread {
 *   private $queue;
 *
 *   public function __construct($name) {
 *     $this->queue= new IPCQueue(8925638);
 *     parent::__construct('receiver');
 *   }
 *
 *   public function run() {
 *     while (0 == $this->queue->getQuantity()) {
 *       Console::writeLinef('<%s> Sleeping...', $this->name);
 *       Thread::sleep(1000);
 *     }
 *
 *     while ($message= $this->queue->getMessage()) {
 *       Console::writeLinef(
 *         "<%s> receiving message:\n -> %s\n",
 *         $this->name, $message->getMessage()->toString()
 *       );
 *       Thread::sleep(1000);
 *     }
 *     Console::writeLinef(
 *       'There are %d messages in queue',
 *       $this->queue->getQuantity()
 *     );
 *     $this->queue->removeQueue();
 *     Console::writeLine("All messages received, queue removed.");
 *   }
 * }
 *
 * $t[0]= new SenderThread(2);
 * $t[0]->start();
 * $t[1]= new ReceiverThread();
 * $t[1]->start();
 * var_dump($t[0]->join(), $t[1]->join());
 * ```
 */
class IPCQueue {
  public
    $key      = 0,
    $id       = 0,
    $stat     = [];

  /**
   * Constructor
   *
   * @param   int System V IPC keys default NULL
   */  
  public function __construct($key= null) {
    $this->key= (null == $key) ? Ftok::get() : $key;
    $this->id= msg_get_queue($this->key, IPC_QUEUE_PERM);
    $this->stat= msg_stat_queue($this->id);
  }
  
  /**
   * Put a message into queue
   *
   * @param   io.sys.IPCMessage msg
   * @param   bool serialize default TRUE
   * @param   bool blocking default TRUE
   * @throws  io.IOException
   */
  public function putMessage($msg, $serialize= true, $blocking= true) {
    if (!msg_send($this->id, $msg->getType(), $msg->getMessage(), $serialize, $blocking, $err)) {
      throw new IOException('Message could not be send. Errorcode '.$err);
    }
  }
  
  /**
   * Get a message from queue
   *
   * @param   int desired messagetype
   * @param   int flags
   * @param   int maxsize default IPC_MSG_MAXSIZE
   * @param   bool serialize default TRUE
   * @throws  io.IOException
   * @return  io.sys.IPCMessage or NULL if no message is available
   */    
  public function getMessage($desiredType= 0, $flags= 0, $maxSize= IPC_MSG_MAXSIZE, $serialize= true) {
  
    // refresh queue
    $this->stat= msg_stat_queue($this->id);
    
    // is a message in queue ?
    if (0 == $this->stat['msg_qnum'] && ($flags & MSG_IPC_NOWAIT)) {
      return null;
    }
    
    // t.b.d. handle message flags and message types
    // see http://de3.php.net/manual/en/function.msg-receive.php
    if (!msg_receive($this->id, $desiredType, $msgType, $maxSize, $msg, $serialize, $flags, $err)) {
      throw new IOException('Message could not be received. Errorcode '.$err);
    }
    return new IPCMessage($msg, $msgType);
  }


  /**
   * Remove a message queue
   *
   * @throws  io.IOException
   */    
  public function removeQueue() {
  
    // refresh queue
    $this->stat= msg_stat_queue($this->id);
    
    if (0 !== $this->stat['msg_qnum']) {
      throw new IOException('Queue cannot be removed. There are unreceived messages.');
    }
    msg_remove_queue($this->id);
  }
  
  /**
   * Get OwnerUID
   *
   * @return  int
   */
  public function getOwnerUID() {
    $this->stat= msg_stat_queue($this->id);
    return $this->stat['msg_perm.uid'];
  }

  /**
   * Get OwnerGID
   *
   * @return  int
   */
  public function getOwnerGID() {
    $this->stat= msg_stat_queue($this->id);
    return $this->stat['msg_perm.gid'];
  }

  /**
   * Get Permissions
   *
   * @return  int
   */
  public function getPermissions() {
    $this->stat= msg_stat_queue($this->id);
    return $this->stat['msg_perm.mode'];
  }

  /**
   * Get SentTime
   *
   * @return  int
   */
  public function getSentTime() {
    $this->stat= msg_stat_queue($this->id);
    return $this->stat['msg_stime'];
  }

  /**
   * Get ReceivedTime
   *
   * @return  int
   */
  public function getReceivedTime() {
    $this->stat= msg_stat_queue($this->id);
    return $this->stat['msg_rtime'];
  }

  /**
   * Get ChangedTime
   *
   * @return  int
   */
  public function getChangedTime() {
    $this->stat= msg_stat_queue($this->id);
    return $this->stat['msg_ctime'];
  }

  /**
   * Get Quantity
   *
   * @return  int
   */
  public function getQuantity() {
    $this->stat= msg_stat_queue($this->id);
    return $this->stat['msg_qnum'];
  }

  /**
   * Get Size
   *
   * @return  int
   */
  public function getSize() {
    $this->stat= msg_stat_queue($this->id);
    return $this->stat['qbytes'];
  }

  /**
   * Get SentPID
   *
   * @return  int
   */
  public function getSentPID() {
    $this->stat= msg_stat_queue($this->id);
    return $this->stat['msg_lspid'];
  }

  /**
   * Get ReceivedPID
   *
   * @return  int
   */
  public function getReceivedPID() {
    $this->stat= msg_stat_queue($this->id);
    return $this->stat['msg_lrpid'];
  }

  /**
   * Get IPC message key
   *
   * @return  int
   */
  public function getKey() {
    return $this->key;
  }
}
