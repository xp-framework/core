<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'util.collections.HashTable'
  );

  /**
   * TestCase for create() core functionality
   *
   * @purpose  Unittest
   */
  class CreateTest extends TestCase {
  
    /**
     * Test create() returns an object passed in, for use in fluent
     * interfaces, e.g.
     *
     * <code>
     *   $c= create(new Criteria())->add('bz_id', 20000, EQUAL);
     * </code>
     *
     * @see   http://xp-framework.info/xml/xp.en_US/news/view?184
     */
    #[@test]
    public function createReturnsObjects() {
      $fixture= new Object();
      $this->assertEquals($fixture, create($fixture));
    }

    /**
     * Test create() using short class names
     *
     */
    #[@test]
    public function createWithShortNames() {
      $h= create('HashTable<String, String>');
      $this->assertEquals(array('String', 'String'), $h->__generic);
    }

    /**
     * Test create() using fully qualified class names
     *
     */
    #[@test]
    public function createWithQualifiedNames() {
      $h= create('util.collections.HashTable<lang.types.String, lang.types.String>');
      $this->assertEquals(array('String', 'String'), $h->__generic);
    }

    /**
     * Test create() with non-generic classes
     *
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function createWithNonGeneric() {
      create('lang.Object<String>');
    }
  }
?>
