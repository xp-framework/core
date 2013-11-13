<?php namespace peer\server\protocol;

/**
 * Server Protocol: Handle out of resources after having accepted
 * a child. 
 *
 * In the ForkingServer implementation, this occurs when a fork() call 
 * fails - in this situation, the server will call an implementation's
 * <tt>handleOutOfResources</tt> method before closing the client socket.
 *
 * @see   xp://peer.server.ForkingServer
 */
interface OutOfResourcesHandler {

  /**
   * Handle out of resources error
   *
   * @param   peer.Socket socket
   * @param   lang.XPException reason
   */
  public function handleOutOfResources($socket, $reason);
}
