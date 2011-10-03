<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'util.Date',
    'lang.types.String',
    'webservices.json.JsonDecoder'
  );

  /**
   * Testcase for JsonDecoder
   *
   * @see   xp://webservices.json.JsonDecoder
   */
  class JsonEncodingTest extends TestCase {
    protected $fixture= NULL;
    protected $tz= NULL;
        
    /**
     * Setup text fixture
     *
     */
    public function setUp() {
      $this->fixture= new JsonDecoder();
      $this->tz= date_default_timezone_get();
      date_default_timezone_set('Europe/Berlin');
    }
    
    /**
     * Tear down test.
     *
     */
    public function tearDown() {
      date_default_timezone_set($this->tz);
    }

    /**
     * Returns encoded object
     *
     * @param   var input
     * @return  string
     */
    protected function encode($input) {
      return $this->fixture->encode($input);
    }
    
    /**
     * Test string encoding
     *
     */
    #[@test]
    public function encodeString() {
      $this->assertEquals('"foo"', $this->encode('foo'));
    }
    
    /**
     * Test string encoding
     *
     */
    #[@test]
    public function encodeUTF8String() {
      $this->assertEquals('"föo"', $this->encode('f�o'));
    }

    /**
     * Test String with quotation mark encoding
     *
     */
     #[@test]
     public function encodeQuotationMarkString() {
       $this->assertEquals('"f\\"o\\"o"', $this->encode('f"o"o'));
     }

     /**
     * Test String with reverse solidus encoding
     *
     */
     #[@test]
     public function encodeReverseSolidusString() {
       $this->assertEquals('"fo\\\\o"', $this->encode('fo\\o'));
     }

     /**
     * Test String with solidus encoding
     *
     */
     #[@test]
     public function encodeSolidusString() {
       $this->assertEquals('"fo\\/o"', $this->encode('fo/o'));
     }

     /**
     * Test String with backspace encoding
     *
     */
     #[@test]
     public function encodeBackspaceString() {
       $this->assertEquals('"fo\\bo"', $this->encode('fo'."\b".'o'));
     }

     /**
     * Test String with formfeed encoding
     *
     */
     #[@test]
     public function encodeFormfeedString() {
       $this->assertEquals('"fo\\fo"', $this->encode('fo'."\f".'o'));
     }

    /**
     * Test string with newline encoding
     *
     */
     #[@test]
     public function encodeNewlineString() {
       $this->assertEquals('"fo\\no"', $this->encode('fo'."\n".'o'));
     }

     /**
     * Test String with carriage return encoding
     *
     */
     #[@test]
     public function encodeCarriageReturnString() {
       $this->assertEquals('"fo\\ro"', $this->encode('fo'."\r".'o'));
     }

     /**
     * Test String with horizontal tab encoding
     *
     */
     #[@test]
     public function encodeHorizontalTabString() {
       $this->assertEquals('"fo\\to"', $this->encode('fo'."\t".'o'));
     }
  
    /**
     * Test positive small integer encoding
     *
     */
    #[@test]
    public function encodePositiveSmallInt() {
      $this->assertEquals('1', $this->encode(1));
    }

    /**
     * Test negative small integer encoding
     *
     */
    #[@test]
    public function encodeNegativeSmallInt() {
      $this->assertEquals('-1', $this->encode(-1));
    }

    /**
     * Test positive big integer encoding
     *
     */
    #[@test]
    public function encodePositiveBigInt() {
      $this->assertEquals('2147483647', $this->encode(2147483647));
    }
    
    /**
     * Test negative big integer encoding
     *
     */
    #[@test]
    public function encodeNegativeBigInt() {
      $this->assertEquals('-2147483647', $this->encode(-2147483647));
    }

    /**
     * Test integer float encoding
     *
     */
    #[@test]
    public function encodeIntegerFloat() {
      $this->assertEquals('1', $this->encode(1.0));
    }

    /**
     * Test small positive float encoding
     *
     */
    #[@test]
    public function encodeSmallPositiveFloat() { 
      $this->assertEquals('1.1', $this->encode(1.1));
    }
    
    /**
     * Test small negative float encoding
     *
     */
    #[@test]
    public function encodeFloat() { 
      $this->assertEquals('-1.1', $this->encode(-1.1));
    }

    /**
     * Test big positive float encoding
     *
     */
    #[@test]
    public function encodeBigPositiveFloat() { 
      $this->assertEquals('9999999999999.1', $this->encode(9999999999999.1));
    }

    /**
     * Test big negative float encoding
     *
     */
    #[@test]
    public function encodeBigNevativeFloat() { 
      $this->assertEquals('-9999999999999.1', $this->encode(-9999999999999.1));
    }

    /**
     * Test very small float encoding
     *
     */
    #[@test]
    public function encodeVerySmallFloat() { 
      $this->assertEquals('1.0E-11', $this->encode(0.00000000001));
    }

    /**
     * Test almost very small float encoding
     *
     */
    #[@test]
    public function encodeAlmostVerySmallFloat() { 
      $this->assertEquals('0.123456789', $this->encode(0.123456789));
    }

    /**
     * Test NULL encoding
     *
     */
    #[@test]
    public function encodeNull() {
      $this->assertEquals('null', $this->encode(NULL));
    }

    /**
     * Test TRUE encoding
     *
     */
    #[@test]
    public function encodeTrue() {
      $this->assertEquals('true', $this->encode(TRUE));
    }

    /**
     * Test FALSE encoding
     *
     */
    #[@test]
    public function encodeFalse() {
      $this->assertEquals('false', $this->encode(FALSE));
    }
    
    /**
     * Test empty array encoding
     *
     */
    #[@test]
    public function encodeEmptyArray() {
      $this->assertEquals('[ ]', $this->encode(array()));
    }

    /**
     * Test simple numeric array encoding
     *
     */
    #[@test]
    public function encodeSimpleNumericArray() {
      $this->assertEquals(
        '[ 1 , 2 , 3 ]',
        $this->encode(array(1, 2, 3))
      );
    }

    /**
     * Test simple mixed array encoding
     *
     */
    #[@test]
    public function encodeSimpleMixedArray() {
      $this->assertEquals(
        '[ "foo" , 2 , "bar" ]',
        $this->encode(array('foo', 2, 'bar'))
      );
    }

    /**
     * Test normal mixed array encoding
     *
     */
    #[@test]
    public function encodeNormalMixedArray() {
      $this->assertEquals(
        '[ "foo" , 0.001 , false , [ 1 , 2 , 3 ] ]',
        $this->encode(array('foo', 0.001, FALSE, array(1, 2, 3)))
      );
    }
       
    /**
     * Test simple hashmap encoding
     *
     */
    #[@test]
    public function encodeSimpleHashmap() {
      $this->assertEquals(
        '{ "foo" : "bar" , "bar" : "baz" }',
        $this->encode(array('foo' => 'bar', 'bar' => 'baz'))
      );
    }

    /**
     * Test complex mixed array encoding
     *
     */
    #[@test]
    public function encodeComplexMixedArray() {
      $this->assertEquals(
       '[ "foo" , true , { "foo" : "bar" , "0" : 2 } ]',
       $this->encode(array('foo', TRUE, array('foo' => 'bar', 2)))
      );
    }

    /**
     * Test complex hashmap encoding
     *
     */
    #[@test]
    public function encodeComplexHashmap() {
      $this->assertEquals(
        '{ "foo" : "bar" , "3" : 0.123 , "4" : false , "array" : [ 1 , "foo" , false ] , '.
        '"array2" : { "0" : true , "bar" : 4 } , "array3" : { "foo" : { "foo" : "bar" } } }',
        $this->encode(array('foo' => 'bar',
          3 => 0.123,
          FALSE,
          "array" => array(1, "foo", FALSE),
          "array2" => array(TRUE, "bar" => 4),
          "array3" => array("foo" => array("foo" => "bar"))
        ))
        );
    }

   /**
    * Test exception
    *
    */
   #[@test]
    public function encodeFileResource() {
      $je= NULL;
      $file= tmpfile();

      try {
        $this->encode($file);
      } catch (JsonException $je) {
        // Do nothing here
      }

      $this->assertInstanceOf(
        'webservices.json.JsonException',
        $je
      );
    }

    /**
     * Additional Tests
     * 
     */

    /**
     * Test Array with only one element
     *
     */
    #[@test]
    public function encodeOneElementArray() {
      $this->assertEquals('[ "foo" ]', $this->encode(array('foo')));
    }

    /**
     * Test Object with only one element
     *
     */
    #[@test]
    public function encodeOneElementObejct() {
      $this->assertEquals(
        '{ "foo" : "bar" }',
        $this->encode(array('foo' => 'bar'))
      );
    }

    /**
     * Even more additional tests
     *
     */

    /**
     * Test encode lang.types.String to Json string
     *
     */
    #[@test]
    public function encodeStringObject() {
      $this->assertEquals(
        '"foobar"',
        $this->encode(new String('foobar'))
      );
    }

    /**
     * Test encode date object
     *
     */
    #[@test]
    public function encodeDateObject() {
      $je= NULL;

      try {
        $this->encode(new Date('2009-05-18 01:02:03'));
      } catch (JsonException $je) {
        // Do nothing here
      }

      $this->assertInstanceOf(
        'webservices.json.JsonException',
        $je
      );
    }


  }
?>