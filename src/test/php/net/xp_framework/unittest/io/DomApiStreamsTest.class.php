<?php namespace net\xp_framework\unittest\io;

use io\streams\{MemoryInputStream, MemoryOutputStream, Streams};
use lang\Runtime;
use unittest\{Assert, Before, PrerequisitesNotMetError, Test};

class DomApiStreamsTest {

  #[Before]
  public function setUp() {
    if (!Runtime::getInstance()->extensionAvailable('dom')) {
      throw new PrerequisitesNotMetError('DOM extension not loaded', null, ['ext/dom']);
    }
  }
 
  #[Test]
  public function usableInLoadHTMLFile() {
    $dom= new \DOMDocument();
    Assert::true($dom->loadHTMLFile(Streams::readableUri(new MemoryInputStream(trim('
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
    Assert::equals('übercoder', $dom->getElementsByTagName('title')->item(0)->nodeValue);
  }

  #[Test]
  public function usableInSaveHTMLFile() {
    $out= new MemoryOutputStream();

    // Create DOM and save it to stream
    $dom= new \DOMDocument();
    $dom->appendChild($dom->createElement('html'))
      ->appendChild($dom->createElement('head'))
      ->appendChild($dom->createElement('title', 'Ubercoder'))
    ;
    $dom->saveHTMLFile(Streams::writeableUri($out));
    
    // Check file contents
    Assert::equals(
      '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>Ubercoder</title></head></html>', 
      trim($out->bytes())
    );
  }

  #[Test]
  public function usableInLoad() {
    $dom= new \DOMDocument();
    Assert::true($dom->load(Streams::readableUri(new MemoryInputStream(trim('
      <?xml version="1.0" encoding="utf-8"?>
      <root>
        <child>übercoder</child>
      </root>
    ')))));
    Assert::equals('übercoder', $dom->getElementsByTagName('child')->item(0)->nodeValue);
  } 

  #[Test]
  public function usableInSave() {
    $out= new MemoryOutputStream();

    // Create DOM and save it to stream
    $dom= new \DOMDocument();
    $dom->appendChild($dom->createElement('root'))->appendChild($dom->createElement('child', 'übercoder'));
    $dom->save(Streams::writeableUri($out));
    
    // Check file contents
    Assert::equals(
      '<?xml version="1.0"?>'."\n".
      '<root><child>&#xFC;bercoder</child></root>',
      trim($out->bytes())
    );
  }
}