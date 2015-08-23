<?php namespace net\xp_framework\unittest\io\collections;

use io\collections\CollectionComposite;
use io\collections\IOElement;
use lang\IllegalArgumentException;

/**
 * Unit tests for CollectionComposite class
 *
 * @see  xp://io.collections.CollectionComposite
 */
class CollectionCompositeTest extends AbstractCollectionTest {

  /**
   * Helper method that asserts a given element is an IOElement
   * and that its URI equals the expected URI.
   *
   * @param   string uri
   * @param   io.collections.IOElement element
   * @throws  unittest.AssertionFailedError
   */
  protected function assertElement($uri, $element) {
    $this->assertInstanceOf(IOElement::class, $element);
    $this->assertEquals($uri, $element->getURI());
  }
  
  /**
   * Returns an empty collection.
   *
   * @param   string name
   * @return  io.collections.IOCollection
   */
  protected function emptyCollection($name) {
    return $this->newCollection($name, []);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function constructorThrowsExceptionForEmptyList() {
    new CollectionComposite([]);
  }

  #[@test]
  public function nextReturnsNullForOneEmptyCollection() {
    $empty= new CollectionComposite([$this->emptyCollection('empty-dir')]);
    $empty->open();
    $this->assertNull($empty->next());
    $empty->close();
  }

  #[@test]
  public function nextReturnsNullForTwoEmptyCollections() {
    $empty= new CollectionComposite([
      $this->emptyCollection('empty-dir'),
      $this->emptyCollection('lost+found')
    ]);
    $empty->open();
    $this->assertNull($empty->next());
    $empty->close();
  }

  #[@test]
  public function elementsFromAllCollections() {
    $composite= new CollectionComposite([
      $this->newCollection('/home', [new MockElement('.nedit')]),
      $this->newCollection('/usr/local/etc', [new MockElement('php.ini')]),
    ]);
    $composite->open();
    $this->assertElement('.nedit', $composite->next());
    $this->assertElement('php.ini', $composite->next());
    $this->assertNull($composite->next());
    $composite->close();
  }
}
