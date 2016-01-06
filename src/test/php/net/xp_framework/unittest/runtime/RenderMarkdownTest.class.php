<?php namespace net\xp_framework\unittest\runtime;

use xp\runtime\RenderMarkdown;

class RenderMarkdownTest extends \unittest\TestCase {
  private $markdown;

  public function setUp() {
    $this->markdown= new RenderMarkdown([
      'h1'     => '<h1>$1</h1>',
      'bold'   => '<b>$1</b>',
      'italic' => '<i>$1</i>',
      'pre'    => '<pre>$1</pre>',
      'code'   => '<code indent="$1" lang="$2">$3</code>',
      'li'     => '<li symbol="$1">$2</li>',
      'hr'     => '<hr/>'
    ]);  
  }

  private function assertMarkdown($expected, $input) {
    $this->assertEquals($expected, $this->markdown->render($input));
  }

  #[@test]
  public function first_level_headline_with_leading_hash() {
    $this->assertMarkdown('<h1>Headline</h1>', '# Headline');
  }

  #[@test]
  public function underlined_first_level_headline() {
    $this->assertMarkdown('<h1>Headline</h1>', "Headline\n=============\n");
  }

  #[@test, @values([
  #  ['<b>Text</b>', '**Text**'],
  #  ['This is <b>bold</b>', 'This is **bold**'],
  #  ['A <b>bold</b> word', 'A **bold** word'],
  #  ['<b>Bold</b> start', '**Bold** start']
  #])]
  public function bold($expected, $input) {
    $this->assertMarkdown($expected, $input);
  }

  #[@test, @values([
  #  ['<i>Text</i>', '*Text*'],
  #  ['This is <i>italic</i>', 'This is *italic*'],
  #  ['A <i>italic</i> word', 'A *italic* word'],
  #  ['<i>Italic</i> start', '*Italic* start']
  #])]
  public function italic($expected, $input) {
    $this->assertMarkdown($expected, $input);
  }

  #[@test, @values([
  #  ['<pre>Text</pre>', '`Text`'],
  #  ['This is <pre>preformatted</pre>', 'This is `preformatted`'],
  #  ['A <pre>preformatted</pre> word', 'A `preformatted` word'],
  #  ['<pre>Preformatted</pre> start', '`Preformatted` start']
  #])]
  public function pre($expected, $input) {
    $this->assertMarkdown($expected, $input);
  }

  #[@test, @values([
  #  ["<hr/>\nBelow", "* * *\nBelow"],
  #  ["Above\n<hr/>", "Above\n* * *"],
  #  ["Above\n<hr/>\nBelow", "Above\n* * *\nBelow"],
  #])]
  public function hr($expected, $input) {
    $this->assertMarkdown($expected, $input);
  }

  #[@test]
  public function fenced_code() {
    $this->assertMarkdown('<code indent="" lang="php">class Test { }</code>', "```php\nclass Test { }\n```");
  }

  #[@test]
  public function indented_fenced_code() {
    $this->assertMarkdown('<code indent="  " lang="sh">$ xp test</code>', "  ```sh\n  \$ xp test\n  ```");
  }

  #[@test, @values(['- Hello', '+ Hello', '* Hello'])]
  public function li($variation) {
    $this->assertMarkdown('<li symbol="'.$variation{0}.'">Hello</li>', $variation);
  }

  #[@test]
  public function lis() {
    $this->assertMarkdown("<li symbol=\"-\">Hello</li>\n<li symbol=\"-\">World</li>", "- Hello\n- World");
  }
}