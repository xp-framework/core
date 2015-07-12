<?php namespace net\xp_framework\unittest\io\collections;

use io\collections\iterate\IOCollectionIterator;
use io\collections\iterate\FilteredIOCollectionIterator;
use io\collections\iterate\AccessedAfterFilter;
use io\collections\iterate\AccessedBeforeFilter;
use io\collections\iterate\CreatedAfterFilter;
use io\collections\iterate\CreatedBeforeFilter;
use io\collections\iterate\IterationFilter;
use io\collections\iterate\ModifiedAfterFilter;
use io\collections\iterate\ModifiedBeforeFilter;
use io\collections\iterate\NameMatchesFilter;
use io\collections\iterate\NameEqualsFilter;
use io\collections\iterate\ExtensionEqualsFilter;
use io\collections\iterate\UriMatchesFilter;
use io\collections\iterate\SizeBiggerThanFilter;
use io\collections\iterate\SizeEqualsFilter;
use io\collections\iterate\SizeSmallerThanFilter;
use util\Filters;

/**
 * Unit tests for I/O collection iterator classes
 *
 * @see   xp://io.collections.IOCollectionIterator
 * @see   xp://io.collections.FilteredIOCollectionIterator
 */
class IOCollectionIteratorTest extends AbstractCollectionTest {

  /**
   * Helper method
   *
   * @param   io.collections.iterate.Filter $filter
   * @param   bool $recursive default FALSE
   * @return  string[] an array of the elements' URIs
   */
  protected function filterFixtureWith($filter, $recursive= false) {
    $elements= [];
    for (
      $it= new FilteredIOCollectionIterator($this->fixture, $filter, $recursive);
      $it->hasNext(); 
    ) {
      $elements[]= $it->next()->getURI();
    }
    return $elements;
  }

  #[@test]
  public function iteration() {
    for ($it= new IOCollectionIterator($this->fixture), $i= 0; $it->hasNext(); $i++) {
      $element= $it->next();
      $this->assertInstanceOf('io.collections.IOElement', $element);
    }
    $this->assertEquals($this->sizes[$this->fixture->getURI()], $i);
  }

  #[@test]
  public function recursiveIteration() {
    for ($it= new IOCollectionIterator($this->fixture, true), $i= 0; $it->hasNext(); $i++) {
      $element= $it->next();
      $this->assertInstanceOf('io.collections.IOElement', $element);
    }
    $this->assertEquals($this->total, $i);
  }

  #[@test]
  public function foreachLoop() {
    foreach (new IOCollectionIterator($this->fixture) as $i => $element) {
      $this->assertInstanceOf('io.collections.IOElement', $element);
    }
    $this->assertEquals($this->sizes[$this->fixture->getURI()]- 1, $i);
  }

  #[@test]
  public function foreachLoopRecursive() {
    foreach (new IOCollectionIterator($this->fixture, true) as $i => $element) {
      $this->assertInstanceOf('io.collections.IOElement', $element);
    }
    $this->assertEquals($this->total- 1, $i);
  }

  #[@test]
  public function filteredIteration() {
    $this->assertEquals(
      $this->sizes[$this->fixture->getURI()],
      sizeof($this->filterFixtureWith(new NullFilter(), false))
    );
  }

  #[@test]
  public function filteredRecursiveIteration() {
    $this->assertEquals(
      $this->total,
      sizeof($this->filterFixtureWith(new NullFilter(), true))
    );
  }

  #[@test]
  public function nameMatches() {
    $this->assertEquals(
      ['./first.txt', './second.txt'],
      $this->filterFixtureWith(new NameMatchesFilter('/\.txt$/'), false)
    );
  }

  #[@test]
  public function nameMatchesRecursive() {
    $this->assertEquals(
      ['./first.txt', './second.txt', './sub/IMG_6100.txt'],
      $this->filterFixtureWith(new NameMatchesFilter('/\.txt$/'), true)
    );
  }

  #[@test]
  public function nameEquals() {
    $this->assertEquals(
      [], 
      $this->filterFixtureWith(new NameEqualsFilter('__xp__.php'), false)
    );
  }

  #[@test]
  public function nameEqualsRecursive() {
    $this->assertEquals(
      ['./sub/sec/__xp__.php'],
      $this->filterFixtureWith(new NameEqualsFilter('__xp__.php'), true)
    );
  }

  #[@test]
  public function extensionEquals() {
    $this->assertEquals(
      [], 
      $this->filterFixtureWith(new ExtensionEqualsFilter('.php'), false)
    );
  }

  #[@test]
  public function extensionEqualsRecursive() {
    $this->assertEquals(
      ['./sub/sec/lang.base.php', './sub/sec/__xp__.php'],
      $this->filterFixtureWith(new ExtensionEqualsFilter('.php'), true)
    );
  }

  #[@test]
  public function uriMatches() {
    $this->assertEquals(
      ['./first.txt', './second.txt'],
      $this->filterFixtureWith(new UriMatchesFilter('/\.txt$/'), false)
    );
  }

  #[@test]
  public function uriMatchesRecursive() {
    $this->assertEquals(
      ['./sub/', './sub/IMG_6100.jpg', './sub/IMG_6100.txt', './sub/sec/', './sub/sec/lang.base.php', './sub/sec/__xp__.php'],
      $this->filterFixtureWith(new UriMatchesFilter('/sub/'), true)
    );
  }

  #[@test]
  public function uriMatchesDirectorySeparators() {
    with ($src= $this->addElement($this->fixture, new MockCollection('./sub/src'))); {
      $this->addElement($src, new MockElement('./sub/src/Generic.xp')); 
    }
    $this->assertEquals(
      ['./sub/src/Generic.xp'],
      $this->filterFixtureWith(new UriMatchesFilter('/sub\/src\/.+/'), true)
    );
  }

  #[@test]
  public function uriMatchesPlatformDirectorySeparators() {
    $mockName= '.'.DIRECTORY_SEPARATOR.'sub'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Generic.xp';
    with ($src= $this->addElement($this->fixture, new MockCollection('.'.DIRECTORY_SEPARATOR.'sub'.DIRECTORY_SEPARATOR.'src'))); {
      $this->addElement($src, new MockElement($mockName));
    }
    $this->assertEquals(
      [$mockName],
      $this->filterFixtureWith(new UriMatchesFilter('/sub\/src\/.+/'), true)
    );
  }
  
  #[@test]
  public function zeroBytes() {
    $this->assertEquals(
      ['./zerobytes.png'],
      $this->filterFixtureWith(new SizeEqualsFilter(0), false)
    );
  }

  #[@test]
  public function bigFiles() {
    $this->assertEquals(
      ['./sub/IMG_6100.jpg'],
      $this->filterFixtureWith(new SizeBiggerThanFilter(500000), true)
    );
  }

  #[@test]
  public function smallFiles() {
    $this->assertEquals(
      ['./second.txt', './zerobytes.png'],
      $this->filterFixtureWith(new SizeSmallerThanFilter(500), true)
    );
  }

  #[@test]
  public function accessedAfter() {
    $this->assertEquals(
      ['./first.txt', './second.txt', './sub/sec/lang.base.php', './sub/sec/__xp__.php'],
      $this->filterFixtureWith(new AccessedAfterFilter(new \util\Date('Oct  1  2006')), true)
    );
  }

  #[@test]
  public function accessedBefore() {
    $this->assertEquals(
      ['./third.jpg', './zerobytes.png'],
      $this->filterFixtureWith(new AccessedBeforeFilter(new \util\Date('Dec 14  2004')), true)
    );
  }

  #[@test]
  public function modifiedAfter() {
    $this->assertEquals(
      ['./sub/sec/lang.base.php', './sub/sec/__xp__.php'],
      $this->filterFixtureWith(new ModifiedAfterFilter(new \util\Date('Oct  7  2006')), true)
    );
  }

  #[@test]
  public function modifiedBefore() {
    $this->assertEquals(
      ['./third.jpg', './zerobytes.png'],
      $this->filterFixtureWith(new ModifiedBeforeFilter(new \util\Date('Dec 14  2004')), true)
    );
  }

  #[@test]
  public function createdAfter() {
    $this->assertEquals(
      ['./sub/sec/__xp__.php'],
      $this->filterFixtureWith(new CreatedAfterFilter(new \util\Date('Jul  1  2006')), true)
    );
  }

  #[@test]
  public function createdBefore() {
    $this->assertEquals(
      ['./sub/sec/lang.base.php'],
      $this->filterFixtureWith(new CreatedBeforeFilter(new \util\Date('Feb 22  2002')), true)
    );
  }

  #[@test]
  public function allOf() {
    $this->assertEquals(
      ['./third.jpg'],
      $this->filterFixtureWith(Filters::allOf([
        new ModifiedBeforeFilter(new \util\Date('Dec 14  2004')),
        new ExtensionEqualsFilter('jpg')
      ]), true)
    );
  }

  #[@test]
  public function anyOf() {
    $this->assertEquals(
      ['./first.txt', './second.txt', './zerobytes.png', './sub/IMG_6100.txt'],
      $this->filterFixtureWith(Filters::anyOf([
        new SizeSmallerThanFilter(500),
        new ExtensionEqualsFilter('txt')
      ]), true)
    );
  }

  #[@test]
  public function noneOf() {
    $this->assertEquals(
      ['./third.jpg', './sub/', './sub/IMG_6100.jpg', './sub/sec/', './sub/sec/lang.base.php', './sub/sec/__xp__.php'],
      $this->filterFixtureWith(Filters::noneOf([
        new SizeSmallerThanFilter(500),
        new ExtensionEqualsFilter('txt')
      ]), true)
    );
  }

  #[@test]
  public function originBasedOn() {
    $c= $this->newCollection('/home', [
      new MockElement('.nedit'),
      $this->newCollection('/home/bin', [
        new MockElement('xp')
      ])
    ]);
    
    foreach (new IOCollectionIterator($c, true) as $i => $e) {
      $this->assertOriginBasedOn($c, $e->getOrigin());
    }
  }

  #[@test]
  public function originEqualsBase() {
    $c= $this->newCollection('/home', [
      new MockElement('.nedit'),
      $this->newCollection('/home/bin', [
        new MockElement('xp')
      ])
    ]);
    
    foreach (new IOCollectionIterator($c) as $i => $e) {
      $this->assertEquals($c, $e->getOrigin());
    }
  }

  #[@test]
  public function originEquals() {
    $c= $this->newCollection('/home', [
      new MockElement('.nedit'),
      $bin= $this->newCollection('/home/bin', [
        new MockElement('xp.exe')
      ])
    ]);
    
    foreach (new FilteredIOCollectionIterator($c, new ExtensionEqualsFilter('.exe'), true) as $i => $e) {
      $this->assertNotEquals($c, $e->getOrigin());
      $this->assertEquals($bin, $e->getOrigin());
    }
  }
}
