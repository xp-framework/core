<?php namespace peer\server;

/**
 * Basic TCP/IP Server using libevent
 *
 * @ext   event
 * @see   http://pecl.php.net/package/event
 */
class EventServer extends Server {
  
  /**
   * Service
   *
   * @return void
   */
  public function service() {
    if (!$this->socket->isConnected()) return false;

    $base= new \EventBase();
    $listener= new \EventListener(
      $base,
      function($listener, $fd, $address, $base) {
        $event= new \EventBufferEvent($base, $fd, \EventBufferEvent::OPT_CLOSE_ON_FREE);
        $event->setCallbacks(
          function($event) {
            $this->protocol->handleData(new EventSocket($event));
          },
          function($event) {
            if (0 === $event->output->length) {
              $event->free();
            }
          },
          function($event, $events) {
            if ($events & (\EventBufferEvent::EOF | \EventBufferEvent::ERROR)) {
              $event->free();
            }
          },
          null
        );
        $event->enable(\Event::READ);
      },
      $base,
      \EventListener::OPT_CLOSE_ON_FREE | \EventListener::OPT_REUSEABLE,
      -1,
      $this->socket->getHandle()
    );

    $base->dispatch();
  }
}
