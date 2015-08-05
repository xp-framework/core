<?php namespace net\xp_framework\unittest\tests;

use unittest\TestCase;
use unittest\web\WebTestCase;
use unittest\PrerequisitesNotMetError;
use io\streams\MemoryInputStream;
use peer\http\HttpConnection;
use peer\http\HttpResponse;
use peer\http\HttpConstants;

/**
 * WebTestCase tests
 *
 * @see   xp://unittest.web.WebTestCase
 */
class WebTestCaseTest extends TestCase {
  protected $fixture= null;

  /** @return void */
  #[@beforeClass]
  public static function verifyDependencies() {
    if (!class_exists('xml\Tree')) {
      throw new PrerequisitesNotMetError('XML Module not available', null, ['loaded']);
    }
    if (!class_exists('peer\http\HttpConnection')) {
      throw new PrerequisitesNotMetError('HTTP Module not available', null, ['loaded']);
    }
  }

  /**
   * Sets up test case
   */
  public function setUp() {
    $this->fixture= newinstance('unittest.web.WebTestCase', [$this->name], [
      'getConnection' => function($url= null) {
        return new HttpConnection('http://localhost/');
      },
      'doRequest' => function($method, $params) {
        return $this->response;
      },
      'respondWith' => function($status, $headers= [], $body= '') {
        $headers[]= 'Content-Length: '.strlen($body);
        $this->response= new HttpResponse(new MemoryInputStream(sprintf(
          "HTTP/1.0 %d Message\r\n%s\r\n\r\n%s",
          $status,
          implode("\r\n", $headers),
          $body
        )));
      }
    ]);
  }

  /**
   * Assertion helper
   *
   * @param   string $action
   * @param   string $method
   * @param   unittest.web.Form form
   * @throws  unittest.AssertionFailedError
   */
  protected function assertForm($action, $method, $form) {
    $this->assertInstanceOf('unittest.web.Form', $form);
    $this->assertEquals($action, $form->getAction());
    $this->assertEquals($method, $form->getMethod());
  }

  /**
   * Returns the form used for testing below
   *
   * @return  string
   */
  protected function formFixture() {
    return trim('
      <html>
        <head>
          <meta charset="utf-8"/>
          <title>Enter your name</title>
        </head>
        <body>
          <form>
            <input type="text" name="first"/>
            <input type="text" name="initial" value=""/>
            <input type="text" name="last" value="Tester"/>
            <input type="text" name="uber" value="Übercoder"/>

            <hr/>
            <select name="gender">
              <option value="-">(select one)</option>
              <option value="M">male</option>
              <option value="F">female</option>
              <option value="U">überwoman</option>
            </select>

            <hr/>
            <select name="payment">
              <option value="V">Visa-Card</option>
              <option value="M">Master-Card</option>
              <option value="C" selected>Cheque</option>
            </select>

            <hr/>
            <textarea name="comments">(Comments)</textarea>

            <hr/>
            <textarea name="umlauts">Übercoder</textarea>
          </form>
        </body>
      </html>
    ');
  }

  #[@test]
  public function emptyDocument() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK);

    $this->fixture->beginAt('/');
    $this->fixture->assertStatus(HttpConstants::STATUS_OK);
    $this->fixture->assertHeader('Content-Length', '0');
  }

  #[@test]
  public function contentType() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, ['Content-Type: text/html']);

    $this->fixture->beginAt('/');
    $this->fixture->assertContentType('text/html');
  }

  #[@test]
  public function contentTypeWithCharset() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, ['Content-Type: text/xml; charset=utf-8']);

    $this->fixture->beginAt('/');
    $this->fixture->assertContentType('text/xml; charset=utf-8');
  }

  #[@test]
  public function errorDocument() {
    $this->fixture->respondWith(HttpConstants::STATUS_NOT_FOUND, [], trim('
      <html>
        <head>
          <title>Not found</title>
        </head>
        <body>
          <h1>404: The file you requested was not found</h1>
        </body>
      </html>
    '));

    $this->fixture->beginAt('/');
    $this->fixture->assertStatus(HttpConstants::STATUS_NOT_FOUND);
    $this->fixture->assertTitleEquals('Not found');
    $this->fixture->assertTextPresent('404: The file you requested was not found');
    $this->fixture->assertTextNotPresent('I found it');
  }

  #[@test]
  public function elements() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], trim('
      <html>
        <head>
          <title>Elements</title>
        </head>
        <body>
          <div id="header"/>
          <!-- <div id="navigation"/> -->
          <div id="main"/>
        </body>
      </html>
    '));

    $this->fixture->beginAt('/');
    $this->fixture->assertElementPresent('header');
    $this->fixture->assertElementNotPresent('footer');
    $this->fixture->assertElementPresent('main');
    $this->fixture->assertElementNotPresent('footer');
  }

  #[@test]
  public function images() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], trim('
      <html>
        <head>
          <title>Images</title>
        </head>
        <body>
          <img src="/static/blank.gif"/>
          <!-- <img src="http://example.com/example.png"/> -->
          <img src="http://example.com/logo.jpg"/>
        </body>
      </html>
    '));

    $this->fixture->beginAt('/');
    $this->fixture->assertImagePresent('/static/blank.gif');
    $this->fixture->assertImageNotPresent('http://example.com/example.png');
    $this->fixture->assertImageNotPresent('logo.jpg');
    $this->fixture->assertImagePresent('http://example.com/logo.jpg');
  }

  #[@test]
  public function links() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], trim('
      <html>
        <head>
          <title>Links</title>
        </head>
        <body>
          <a href="http://example.com/test">Test</a>
          <a href="/does-not-exist">404</a>
          <!-- <a href="comment.html">Hidden</a> -->
        </body>
      </html>
    '));

    $this->fixture->beginAt('/');
    $this->fixture->assertLinkPresent('http://example.com/test');
    $this->fixture->assertLinkPresent('/does-not-exist');
    $this->fixture->assertLinkNotPresent('comment.html');
    $this->fixture->assertLinkNotPresent('index.html');
  }

  #[@test]
  public function linksWithText() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], trim('
      <html>
        <head>
          <title>Links</title>
        </head>
        <body>
          <a href="http://example.com/test">Test</a>
          <a href="/does-not-exist">404</a>
          <!-- <a href="comment.html">Hidden</a> -->
        </body>
      </html>
    '));

    $this->fixture->beginAt('/');
    $this->fixture->assertLinkPresentWithText('Test');
    $this->fixture->assertLinkPresentWithText('404');
    $this->fixture->assertLinkNotPresentWithText('Hidden');
    $this->fixture->assertLinkNotPresentWithText('Hello');
  }

  #[@test]
  public function unnamedForm() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], trim('
      <html>
        <head>
          <title>Enter your name</title>
        </head>
        <body>
          <form action="http://example.com/"/>
        </body>
      </html>
    '));

    $this->fixture->beginAt('/');
    $this->fixture->assertFormPresent();
  }

  #[@test]
  public function noForm() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], trim('
      <html>
        <head>
          <title>Enter your name</title>
        </head>
        <body>
          <!-- TODO: Add form -->
        </body>
      </html>
    '));

    $this->fixture->beginAt('/');
    $this->fixture->assertFormNotPresent();
  }

  #[@test]
  public function namedForms() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], trim('
      <html>
        <head>
          <title>Blue or red pill?</title>
        </head>
        <body>
          <form name="blue" action="http://example.com/one"/>
          <form name="red" action="http://example.com/two"/>
        </body>
      </html>
    '));

    $this->fixture->beginAt('/');
    $this->fixture->assertFormPresent('red');
    $this->fixture->assertFormPresent('blue');
    $this->fixture->assertFormNotPresent('green');
  }
  
  #[@test]
  public function getForm() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], trim('
      <html>
        <head>
          <title>Form-Mania!</title>
        </head>
        <body>
          <form name="one" action="http://example.com/one"></form>
          <form name="two" method="POST" action="http://example.com/two"></form>
          <form name="three"></form>
        </body>
      </html>
    '));

    $this->fixture->beginAt('/');

    $this->assertForm('http://example.com/one', HttpConstants::GET, $this->fixture->getForm('one'));
    $this->assertForm('http://example.com/two', HttpConstants::POST, $this->fixture->getForm('two'));
    $this->assertForm('/', HttpConstants::GET, $this->fixture->getForm('three'));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function nonExistantField() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    $this->fixture->getForm()->getField('does-not-exist');
  }

  #[@test]
  public function textFieldWithoutValue() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    with ($f= $this->fixture->getForm()->getField('first')); {
      $this->assertInstanceOf('unittest.web.InputField', $f);
      $this->assertEquals('first', $f->getName());
      $this->assertEquals(null, $f->getValue());
    }
  }

  #[@test]
  public function textFieldWithEmptyValue() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    with ($f= $this->fixture->getForm()->getField('initial')); {
      $this->assertInstanceOf('unittest.web.InputField', $f);
      $this->assertEquals('initial', $f->getName());
      $this->assertEquals('', $f->getValue());
    }
  }

  #[@test]
  public function textFieldWithValue() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    with ($f= $this->fixture->getForm()->getField('last')); {
      $this->assertInstanceOf('unittest.web.InputField', $f);
      $this->assertEquals('last', $f->getName());
      $this->assertEquals('Tester', $f->getValue());
    }
  }

  #[@test]
  public function textFieldWithUmlautInValue() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    with ($f= $this->fixture->getForm()->getField('uber')); {
      $this->assertInstanceOf('unittest.web.InputField', $f);
      $this->assertEquals('uber', $f->getName());
      $this->assertEquals('Übercoder', $f->getValue());
    }
  }

  #[@test]
  public function selectFieldWithoutSelected() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    with ($f= $this->fixture->getForm()->getField('gender')); {
      $this->assertInstanceOf('unittest.web.SelectField', $f);
      $this->assertEquals('gender', $f->getName());
      $this->assertEquals('-', $f->getValue());
    }
  }

  #[@test]
  public function selectFieldWithSelected() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    with ($f= $this->fixture->getForm()->getField('payment')); {
      $this->assertInstanceOf('unittest.web.SelectField', $f);
      $this->assertEquals('payment', $f->getName());
      $this->assertEquals('C', $f->getValue());
    }
  }

  #[@test]
  public function selectFieldOptions() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    with ($options= $this->fixture->getForm()->getField('gender')->getOptions()); {
      $this->assertEquals(4, sizeof($options));

      $this->assertEquals('-', $options[0]->getValue());
      $this->assertEquals('(select one)', $options[0]->getText());
      $this->assertFalse($options[0]->isSelected());

      $this->assertEquals('M', $options[1]->getValue());
      $this->assertEquals('male', $options[1]->getText());
      $this->assertFalse($options[1]->isSelected());

      $this->assertEquals('F', $options[2]->getValue());
      $this->assertEquals('female', $options[2]->getText());
      $this->assertFalse($options[2]->isSelected());

      $this->assertEquals('U', $options[3]->getValue());
      $this->assertEquals('überwoman', $options[3]->getText());
      $this->assertFalse($options[3]->isSelected());
    }
  }

  #[@test]
  public function selectFieldNoSelectedOptions() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    $this->assertEquals([], $this->fixture->getForm()->getField('gender')->getSelectedOptions());
  }
  
  #[@test]
  public function selectFieldSelectedOptions() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    with ($options= $this->fixture->getForm()->getField('payment')->getSelectedOptions()); {
      $this->assertEquals(1, sizeof($options));

      $this->assertEquals('C', $options[0]->getValue());
      $this->assertEquals('Cheque', $options[0]->getText());
      $this->assertTrue($options[0]->isSelected());
    }
  }

  #[@test]
  public function textArea() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    with ($f= $this->fixture->getForm()->getField('comments')); {
      $this->assertInstanceOf('unittest.web.TextAreaField', $f);
      $this->assertEquals('comments', $f->getName());
      $this->assertEquals('(Comments)', $f->getValue());
    }

  }
  #[@test]
  public function textAreaWithUmlautInValue() {
    $this->fixture->respondWith(HttpConstants::STATUS_OK, [], $this->formFixture());
    $this->fixture->beginAt('/');

    with ($f= $this->fixture->getForm()->getField('umlauts')); {
      $this->assertInstanceOf('unittest.web.TextAreaField', $f);
      $this->assertEquals('umlauts', $f->getName());
      $this->assertEquals('Übercoder', $f->getValue());
    }
  }
}
