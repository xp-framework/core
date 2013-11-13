<?php namespace util\log\layout;

use util\log\Layout;


/**
 * Default layout
 *
 * @test  xp://net.xp_framework.unittest.logging.DefaultLayoutTest
 */
class DefaultLayout extends Layout {

  /**
   * Creates a string representation of the given argument. For any 
   * string given, the result is the string itself, for any other type,
   * the result is the xp::stringOf() output.
   *
   * @param   var arg
   * @return  string
   */
  protected function stringOf($arg) {
    return is_string($arg) ? $arg : \xp::stringOf($arg);
  }

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
      implode(' ', array_map(array($this, 'stringOf'), $event->getArguments()))
    );
  }
}
