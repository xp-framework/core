<?php namespace util\log\layout;

/**
 * Pattern layout
 *
 * Format string
 * -------------
 * The format string consists of format tokens preceded by a percent
 * sign (%) and any other character. The following format tokens are 
 * supported:
 * <ul>
 *   <li>%m - Message</li>
 *   <li>%c - Category name</li>
 *   <li>%l - Log level - lowercase</li>
 *   <li>%L - Log level - uppercase</li>
 *   <li>%t - Time in HH:MM:SS</li>
 *   <li>%p - Process ID</li>
 *   <li>%% - A literal percent sign (%)</li>
 *   <li>%n - A line break</li>
 *   <li>%x - Context information, if available</li>
 * </ul>
 *
 * @test    xp://net.xp_framework.unittest.logging.PatternLayoutTest
 */
class PatternLayout extends \util\log\Layout {
  protected $format= [];

  /**
   * Creates a new pattern layout
   *
   * @param   string format
   */
  public function __construct($format) {
    for ($i= 0, $s= strlen($format); $i < $s; $i++) {
      if ('%' === $format{$i}) {
        if (++$i >= $s) {
          throw new \lang\IllegalArgumentException('Not enough input at position '.($i - 1));
        }
        switch ($format{$i}) {
          case '%': {   // Literal percent
            $this->format[]= '%'; 
            break;
          }
          case 'n': {
            $this->format[]= "\n"; 
            break;
          }
          default: {    // Any other character - verify it's supported
            if (!strspn($format{$i}, 'mclLtpx')) {
              throw new \lang\IllegalArgumentException('Unknown format token "'.$format{$i}.'"');
            }
            $this->format[]= '%'.$format{$i};
          }
        }
      } else {
        $this->format[]= $format{$i};
      }
    }
  }

  /**
   * Formats a logging event according to this layout
   *
   * @param   util.log.LoggingEvent event
   * @return  string
   */
  public function format(\util\log\LoggingEvent $event) {
    $out= '';
    foreach ($this->format as $token) {
      switch ($token) {
        case '%m': $out.= implode(' ', array_map([$this, 'stringOf'], $event->getArguments())); break;
        case '%t': $out.= gmdate('H:i:s', $event->getTimestamp()); break;
        case '%c': $out.= $event->getCategory()->identifier; break;
        case '%l': $out.= strtolower(\util\log\LogLevel::nameOf($event->getLevel())); break;
        case '%L': $out.= strtoupper(\util\log\LogLevel::nameOf($event->getLevel())); break;
        case '%p': $out.= $event->getProcessId(); break;
        case '%x': $out.= null == ($context= $event->getContext()) ? '' : $context->format(); break;
        default: $out.= $token;
      }
    }
    return $out;
  }

  /**
   * Creates a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'("'.implode('', $this->format).'")';
  }
}
