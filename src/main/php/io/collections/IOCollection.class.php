<?php namespace io\collections;

/**
 * IO Collection interface
 */
interface IOCollection extends IOElement {

  /**
   * Open this collection
   *
   */
  public function open();

  /**
   * Rewind this collection (reset internal pointer to beginning of list)
   *
   */
  public function rewind();

  /**
   * Retrieve next element in collection. Return NULL if no more entries
   * are available
   *
   * @return  io.collections.IOElement
   */
  public function next();

  /**
   * Close this collection
   *
   */
  public function close();

}
