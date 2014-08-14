<?php namespace util\log\layout;

/**
 * Default layout
 *
 * @test  xp://net.xp_framework.unittest.logging.DefaultLayoutTest
 */
class DefaultLayout extends \util\log\Layout {

  /**
   * Formats a logging event according to this layout
   *
   * @param   util.log.LoggingEvent event
   * @return  string
   */
  public function format(\util\log\LoggingEvent $event) {
    return sprintf(
      "[%s %5d %5s] %s%s\n", 
      date('H:i:s', $event->getTimestamp()),
      $event->getProcessId(),
      strtolower(\util\log\LogLevel::nameOf($event->getLevel())),
      null === ($context= $event->getContext()) ? '' : $context->format().' ',
      implode(' ', array_map([$this, 'stringOf'], $event->getArguments()))
    );
  }
}
