<?php namespace util\cmd;

/**
 * Base class for all commands
 *
 * @deprecated  See https://github.com/xp-framework/rfc/issues/307
 */
abstract class Command extends \lang\Object implements \lang\Runnable {
  public
    #[@type('io.streams.StringReader')]
    $in  = null,
    #[@type('io.streams.StringWriter')]
    $out = null,
    #[@type('io.streams.StringWriter')]
    $err = null;
  
}
