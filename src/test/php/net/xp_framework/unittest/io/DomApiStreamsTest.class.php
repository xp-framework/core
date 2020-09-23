<?php namespace net\xp_framework\unittest\io;

use io\streams\{MemoryInputStream, MemoryOutputStream, Streams};
use lang\Runtime;
use unittest\{PrerequisitesNotMetError, Test};

/**
 * TestCase
 *
 * @see   php://DOMDocument
 * @see   xp://io.streams.Streams
 */
class DomApiStreamsTest extends \unittest\TestCase {

  /**
   * Sets up this unittest 
   *
   * @throws  unittest.PrerequisitesNotMetError
   */
  public function setUp() {
    if (!Runtime::getInstance()->extensionAvailable('dom')) {
      throw new PrerequisitesNotMetError('DOM extension not loaded', null, ['ext/dom']);
    }
  }
 
  /**
   * Test DOMDocument::loadHTMLFile()
   *
   */
  #[Test]
  public function usableInLoadHTMLFile() {
    $dom= new \DOMDocument();
    $this->assertTrue($dom->loadHTMLFile(Streams::readableUri(new MemoryInputStream(trim('
      <html>
        <head>
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
          <title>übercoder</title>
        </head>
        <body>
          <!-- Content here -->
        </body>
      </html>
    ')))));
    $this->assertEquals('übercoder', $dom->getElementsByTagName('title')->item(0)->nodeValue);
  }

  /**
   * Test DOMDocument::saveHTMLFile()
   *
   */
  #[Test]
  public function usableInSaveHTMLFile() {
    $out= new MemoryOutputStream();

    // Create DOM and save it to stream
    $dom= new \DOMDocument();
    $dom->appendChild($dom->createElement('html'))
      ->appendChild($dom->createElement('head'))
      ->appendChild($dom->createElement('title', 'übercoder'))
    ;
    $dom->saveHTMLFile(Streams::writeableUri($out));
    
    // Check file contents
    $this->assertEquals(
      '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>&uuml;bercoder</title></head></html>', 
      trim($out->bytes())
    );
  }

  /**
   * Test DOMDocument::load()
   *
   */
  #[Test]
  public function usableInLoad() {
    $dom= new \DOMDocument();
    $this->assertTrue($dom->load(Streams::readableUri(new MemoryInputStream(trim('
      <?xml version="1.0" encoding="utf-8"?>
      <root>
        <child>übercoder</child>
      </root>
    ')))));
    $this->assertEquals('übercoder', $dom->getElementsByTagName('child')->item(0)->nodeValue);
  } 
  /**
   * Test DOMDocument::save()
   *
   */
  #[Test]
  public function usableInSave() {
    $out= new MemoryOutputStream();

    // Create DOM and save it to stream
    $dom= new \DOMDocument();
    $dom->appendChild($dom->createElement('root'))->appendChild($dom->createElement('child', 'übercoder'));
    $dom->save(Streams::writeableUri($out));
    
    // Check file contents
    $this->assertEquals(
      '<?xml version="1.0"?>'."\n".
      '<root><child>&#xFC;bercoder</child></root>',
      trim($out->bytes())
    );
  }
}