<?php namespace util\log;

use util\Configurable;

/**
 * A singleton logger
 * 
 * Example output:
 * <pre>
 * [20:45:30 16012 info ] Starting work on 2002/05/29/ 
 * [20:45:30 16012 info ] Done, 0 order(s) processed, 0 error(s) occured 
 * [20:45:30 16012 info ] Finish 
 * </pre>
 *
 * The format of the line prefix (noted in square brackets above) can be configured by:
 * <ul>
 *   <li>the identifier (an id which has recognizing value, e.g. the PID)</li>
 *   <li>the variable "format" (a printf-string)</li>
 * </ul>
 *
 * Note:
 * <ul>
 *   <li>The identifier defaults to the PID of the current process</li>
 *   <li>
 *     The argument order for the format parameter is:<br/>
 *     1) Current date<br/>
 *     2) Identifier<br/>
 *     3) Indicator [info, warn, error, debug]<br/>
 *   </li>
 *   <li>The format string defaults to "[%1$s %2$s %3$s]"</li>
 *   <li>The date format defaults to "H:i:s"</li>
 * </ul>
 *
 * Example [Setting up a logger]:
 * <code>
 *   $cat= Logger::getInstance()->getCategory();
 *   $cat->addAppender(new FileAppender('php://stderr'));
 * </code>
 *
 * Example [Configuring a logger]:
 * <code>
 *   Logger::getInstance()->configure(new Properties('etc/log.ini'));
 * </code>
 *
 * Example [Usage somewhere later on]:
 * <code>
 *   $cat= Logger::getInstance()->getCategory();
 * </code>
 *
 * Property file sample:
 * <pre>
 * [default]
 * appenders="util.log.FileAppender"
 * appender.util.log.FileAppender.params="filename"
 * appender.util.log.FileAppender.param.filename="/var/log/xp/service_%Y-%m-%d.log"
 * appender.util.log.FileAppender.levels="ERROR|WARN"
 * 
 * [info.binford6100.webservices.EventHandler]
 * appenders="util.log.FileAppender"
 * appender.util.log.FileAppender.params="filename"
 * appender.util.log.FileAppender.param.filename="/var/log/xp/event_%Y-%m-%d.log"
 * 
 * [info.binford6100.webservices.SubscriberHandler]
 * appenders="util.log.FileAppender"
 * appender.util.log.FileAppender.params="filename"
 * appender.util.log.FileAppender.param.filename="/var/log/xp/subscribe_%Y-%m-%d.log"
 *
 * [info.binford6100.webservices.context]
 * appenders="util.log.FileAppender"
 * context="util.log.context.MappedLogContext"
 * appender.util.log.FileAppender.params="filename"
 * appender.util.log.FileAppender.param.filename="/var/log/xp/service_%Y-%m-%d.log"
 * </pre>
 *
 * @test     xp://net.xp_framework.unittest.logging.LoggerTest
 */
class Logger extends \lang\Object implements Configurable {
  const DFLT= "default";

  protected static $instance= null;
  protected $category= [];
  protected $_finalized= false;

  static function __static() {
    self::$instance= new self();
  }

  /**
   * Constructor.
   *
   */
  protected function __construct() {
    $this->category[self::DFLT]= new LogCategory(self::DFLT);
  }

  /**
   * Get a category
   *
   * @param   string name default self::DFLT
   * @return  util.log.LogCategory
   */ 
  public function getCategory($name= self::DFLT) {
    if (!isset($this->category[$name])) $name= self::DFLT;
    return $this->category[$name];
  }

  /**
   * Get list of all categories
   *
   * @return util.log.LogCategory[]
   */
  public function getCategories() {
  	return $this->category;
  }

  /**
   * Configure this logger
   *
   * @param   util.Properties prop instance of a Properties object
   */
  public function configure($prop) {
    
    // Read all other properties
    $section= $prop->getFirstSection();
    do {
      $this->category[$section]= \lang\XPClass::forName(
        $prop->readString($section, 'category', 'util.log.LogCategory')
      )->newInstance(
        $section,
        $prop->readInteger($section, 'flags', LogLevel::ALL)
      );
      
      // Configure appenders
      $appenders= $prop->readArray($section, 'appenders', []);

      // Go through all of the appenders, loading classes as necessary
      foreach ($appenders as $appender) {
        
        // Read levels (alternatively, for BC, read "flags" setting)
        $levels= $prop->readArray($section, 'appender.'.$appender.'.levels');
        if (!empty($levels)) {
          $flags= 0;
          foreach ($levels as $name) {
            $flags |= LogLevel::named($name);
          }
        } else {
          $flags= $prop->readArray($section, 'appender.'.$appender.'.flags', LogLevel::ALL);
          if (!is_int($flags)) {
            $arrflags= $flags; $flags= 0;
            foreach ($arrflags as $f) {
              try {
                $flags |= LogLevel::named(substr($f, 12)); // 12 = strlen('LOGGER_FLAG_')
              } catch (\lang\IllegalArgumentException $ignore) {
                // ...
              }
            }
          }
        }
        
        $a= $this->category[$section]->addAppender(
          \lang\XPClass::forName($appender)->newInstance(),
          $flags
        );
        
        // Params
        $params= $prop->readArray($section, 'appender.'.$appender.'.params', []);
        foreach ($params as $param) {
          $a->{$param}= $prop->readString(
            $section,
            'appender.'.$appender.'.param.'.$param,
            ''
          );
        }

        // Layout
        if ($layout= $prop->readArray($section, 'appender.'.$appender.'.layout')) {
          $class= \lang\XPClass::forName(array_shift($layout));
          if ($class->hasConstructor()) {
            $a->setLayout($class->getConstructor()->newInstance($layout));
          } else {
            $a->setLayout($class->newInstance());
          }
        }

        // Set context
        if ('' !== ($contextFQN= $prop->readString($section, 'context', ''))) {
          $this->category[$section]->setContext(
            \lang\XPClass::forName($contextFQN)->newInstance()
          );
        }
      }
    } while ($section= $prop->getNextSection());
  }
  
  /**
   * Tells all categories to finalize themselves
   *
   */
  public function finalize() {
    if (!$this->_finalized) foreach ($this->category as $category) {
      $category->finalize();
    }
    $this->_finalized= true;
  }
  
  /**
   * Returns an instance of this class
   *
   * @return  util.log.Logger a logger object
   */
  public static function getInstance() {
    return self::$instance;
  }
} 
