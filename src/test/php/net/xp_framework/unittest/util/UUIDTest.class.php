<?php namespace net\xp_framework\unittest\util;

use lang\FormatException;
use unittest\{Expect, Test, TestCase};
use util\{Bytes, UUID};

class UUIDTest extends TestCase {
  private $fixture;

  /** @return void */
  public function setUp() {
    $this->fixture= new UUID('6ba7b811-9dad-11d1-80b4-00c04fd430c8');
  }

  #[Test]
  public function stringRepresentation() {
    $this->assertEquals('{6ba7b811-9dad-11d1-80b4-00c04fd430c8}', $this->fixture->toString());
  }

  #[Test]
  public function stringCast() {
    $this->assertEquals('6ba7b811-9dad-11d1-80b4-00c04fd430c8', (string)$this->fixture);
  }

  #[Test]
  public function urnRepresentation() {
    $this->assertEquals('urn:uuid:6ba7b811-9dad-11d1-80b4-00c04fd430c8', $this->fixture->getUrn());
  }

  #[Test]
  public function getBytes() {
    $this->assertEquals(
      new Bytes("k\xa7\xb8\x11\x9d\xad\x11\xd1\x80\xb4\x00\xc0O\xd40\xc8"),
      $this->fixture->getBytes()
    );
  }

  #[Test]
  public function hashCodeMethod() {
    $this->assertEquals('6ba7b811-9dad-11d1-80b4-00c04fd430c8', $this->fixture->hashCode());
  }

  #[Test]
  public function node() {
    $this->assertEquals([0, 192, 79, 212, 48, 200], $this->fixture->node);
  }

  #[Test]
  public function fixtureEqualToSelf() {
    $this->assertEquals($this->fixture, $this->fixture);
  }

  #[Test]
  public function fixtureEqualToUpperCaseSelf() {
    $this->assertEquals($this->fixture, new UUID('6BA7B811-9DAD-11D1-80B4-00C04FD430C8'));
  }

  #[Test]
  public function fixtureEqualToBracedSelf() {
    $this->assertEquals($this->fixture, new UUID('{6ba7b811-9dad-11d1-80b4-00c04fd430c8}'));
  }

  #[Test]
  public function fixtureEqualToUrnNotation() {
    $this->assertEquals($this->fixture, new UUID('urn:uuid:6ba7b811-9dad-11d1-80b4-00c04fd430c8'));
  }

  #[Test]
  public function fixtureEqualsToBytes() {
    $this->assertEquals($this->fixture, new UUID(new Bytes("k\xa7\xb8\x11\x9d\xad\x11\xd1\x80\xb4\x00\xc0O\xd40\xc8")));
  }

  #[Test, Expect(FormatException::class)]
  public function emptyInput() {
    new UUID('');
  }

  #[Test, Expect(FormatException::class)]
  public function malFormedMissingOctets() {
    new UUID('00000000-0000-0000-c000');
  }

  #[Test, Expect(FormatException::class)]
  public function malFormedNonHexOctets() {
    new UUID('00000000-0000-0000-c000-XXXXXXXXXXXX');
  }

  #[Test, Expect(FormatException::class)]
  public function emptyBracedNotation() {
    new UUID('{}');
  }

  #[Test, Expect(FormatException::class)]
  public function malFormedBracedNotationMissingOctets() {
    new UUID('{00000000-0000-0000-c000}');
  }

  #[Test, Expect(FormatException::class)]
  public function malFormedBracedNotationNonHexOctets() {
    new UUID('{00000000-0000-0000-c000-XXXXXXXXXXXX}');
  }

  #[Test, Expect(FormatException::class)]
  public function emptyUrnNotation() {
    new UUID('urn:uuid:');
  }

  #[Test, Expect(FormatException::class)]
  public function malFormedUrnNotationMissingOctets() {
    new UUID('urn:uuid:00000000-0000-0000-c000');
  }


  #[Test, Expect(FormatException::class)]
  public function malFormedUrnNotationNonHexOctets() {
    new UUID('urn:uuid:00000000-0000-0000-c000-XXXXXXXXXXXX');
  }

  #[Test]
  public function timeUUID() {
    $this->assertEquals(1, UUID::timeUUID()->version());
  }

  #[Test]
  public function timeUUIDNotEqualToFixture() {
    $this->assertNotEquals($this->fixture, UUID::timeUUID());
  }

  #[Test]
  public function twoTimeUUIDsNotEqual() {
    $this->assertNotEquals(UUID::timeUUID(), UUID::timeUUID());
  }

  #[Test]
  public function randomUUID() {
    $this->assertEquals(4, UUID::randomUUID()->version());
  }

  #[Test]
  public function randomUUIDNotEqualToFixture() {
    $this->assertNotEquals($this->fixture, UUID::randomUUID());
  }

  #[Test]
  public function twoRandomUUIDsNotEqual() {
    $this->assertNotEquals(UUID::randomUUID(), UUID::randomUUID());
  }

  #[Test]
  public function md5UUID() {
    $this->assertEquals(3, UUID::md5UUID(UUID::$NS_DNS, 'example.com')->version());
  }

  #[Test]
  public function md5ExampleDotComWithDnsNs() {
    $this->assertEquals(
      '9073926b-929f-31c2-abc9-fad77ae3e8eb', 
      UUID::md5UUID(UUID::$NS_DNS, 'example.com')->hashCode()
    );
  }

  #[Test]
  public function sha1UUID() {
    $this->assertEquals(5, UUID::sha1UUID(UUID::$NS_DNS, 'example.com')->version());
  }

  #[Test]
  public function sha1ExampleDotComWithDnsNs() {
    $this->assertEquals(
      'cfbff0d1-9375-5685-968c-48ce8b15ae17', 
      UUID::sha1UUID(UUID::$NS_DNS, 'example.com')->hashCode()
    );
  }

  #[Test]
  public function version() {
    $this->assertEquals(1, $this->fixture->version());
  }

  #[Test]
  public function serialization() {
    $this->assertEquals(
      'O:9:"util\UUID":1:{s:5:"value";s:36:"6ba7b811-9dad-11d1-80b4-00c04fd430c8";}', 
      serialize($this->fixture)
    );
  }

  #[Test]
  public function deserialization() {
    $this->assertEquals(
      $this->fixture,
      unserialize('O:9:"util\UUID":1:{s:5:"value";s:36:"6ba7b811-9dad-11d1-80b4-00c04fd430c8";}')
    );
  }
}