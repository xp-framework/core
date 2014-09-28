<?php namespace io;

/**
 * A channel is a means for bidirectional communication and provides
 * both an input stream for reading and an output stream for writing.
 *
 * @see  xp://io.File
 * @see  xp://peer.Socket
 */
interface Channel {

  /** @return io.streams.InputStream */
  public function in();

  /** @return io.streams.OutputStream */
  public function out();
}