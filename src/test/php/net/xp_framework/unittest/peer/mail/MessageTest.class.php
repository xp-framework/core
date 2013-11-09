<?php namespace net\xp_framework\unittest\peer\mail;

use peer\mail\Message;

/**
 * Tests Message class
 */
class MessageTest extends AbstractMessageTest {

  /**
   * Returns a new fixture
   *
   * @return  peer.mail.Message
   */
  protected function newFixture() {
    return new Message();
  }

  #[@test]
  public function default_headers_returned_by_getHeaderString() {
    $this->fixture->setHeader('X-Common-Header', 'test');
    $this->assertEquals(
      "X-Common-Header: test\n".
      "Content-Type: text/plain;\n".
      "\tcharset=\"iso-8859-1\"\n".
      "Mime-Version: 1.0\n".
      "Content-Transfer-Encoding: 8bit\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$this->fixture->getDate()->toString('r')."\n",
      $this->fixture->getHeaderString()
    );
  }

}