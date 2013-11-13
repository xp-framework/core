<?php namespace util\cmd;/* This file is part of the XP framework's experiments
 *
 * $Id$
 */

use lang\Runnable;


/**
 * Base class for all commands
 *
 * @purpose  Abstract base class
 */
abstract class Command extends \lang\Object implements Runnable {
  public
    #[@type('io.streams.StringReader')]
    $in  = null,
    #[@type('io.streams.StringWriter')]
    $out = null,
    #[@type('io.streams.StringWriter')]
    $err = null;
  
}
