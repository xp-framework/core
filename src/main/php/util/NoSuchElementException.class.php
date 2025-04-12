<?php namespace util;

use lang\XPException;

/**
 * Thrown by the next method of an Iterator to indicate that 
 * there are no more elements.
 *
 * @see   util.Iterator
 */
class NoSuchElementException extends XPException {

}