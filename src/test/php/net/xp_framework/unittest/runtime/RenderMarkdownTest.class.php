<?php namespace net\xp_framework\unittest\runtime;

use unittest\{Assert, Test, Values};
use xp\runtime\RenderMarkdown;

class RenderMarkdownTest {
  private $markdown;

  /** @return void */
  #[Before]
  public function setUp() {
    $this->markdown= new RenderMarkdown([
      'h1'     => '<h1>$1</h1>',
      'link'   => '<a href="$2">$1</a>',
      'bold'   => '<b>$1</b>',
      'italic' => '<i>$1</i>',
      'pre'    => '<pre>$1</pre>',
      'code'   => '<code indent="$1" lang="$2">$3</code>',
      'li'     => '<li symbol="$1">$2</li>',
      'hr'     => '<hr/>'
    ]);  
  }

  private function assertMarkdown($expected, $input) {
    Assert::equals($expected, $this->markdown->render($input));
  }

  #[Test]
  public function first_level_headline_with_leading_hash() {
    $this->assertMarkdown('<h1>Headline</h1>', '# Headline');
  }

  #[Test]
  public function underlined_first_level_headline() {
    $this->assertMarkdown('<h1>Headline</h1>', "Headline\n=============\n");
  }

  #[Test, Values([['<a href="text">Text</a>', '[Text](text)'], ['<a href="">Text</a>', '[Text]()'], ['<a href="prev">«</a> <a href="next">»</a>', '[«](prev) [»](next)'], ['<li symbol="*"><a href="next">Next</a></li>', '* [Next](next)']])]
  public function link($expected, $input) {
    $this->assertMarkdown($expected, $input);
  }

  #[Test, Values([['<b>Text</b>', '**Text**'], ['<b>T</b>', '**T**'], ['This is <b>bold</b>', 'This is **bold**'], ['A <b>bold</b> word', 'A **bold** word'], ['<b>Bold</b> start', '**Bold** start']])]
  public function bold($expected, $input) {
    $this->assertMarkdown($expected, $input);
  }

  #[Test, Values([['<i>Text</i>', '*Text*'], ['<i>T</i>', '*T*'], ['This is <i>italic</i>', 'This is *italic*'], ['A <i>italic</i> word', 'A *italic* word'], ['<i>Italic</i> start', '*Italic* start']])]
  public function italic($expected, $input) {
    $this->assertMarkdown($expected, $input);
  }

  #[Test, Values(['a * b', 'a * b *', 'a * b*', ' * a*b', ' * a * b', "First line*\nSecond line*"])]
  public function not_italic($input) {
    $this->assertMarkdown($input, $input);
  }

  #[Test, Values(['a ** b', 'a ** b **', 'a ** b**', ' ** a**b', ' ** a ** b', "First line**\nSecond line**"])]
  public function not_bold($input) {
    $this->assertMarkdown($input, $input);
  }

  #[Test, Values([['<pre>Text</pre>', '`Text`'], ['This is <pre>preformatted</pre>', 'This is `preformatted`'], ['A <pre>preformatted</pre> word', 'A `preformatted` word'], ['<pre>Preformatted</pre> start', '`Preformatted` start']])]
  public function pre($expected, $input) {
    $this->assertMarkdown($expected, $input);
  }

  #[Test, Values([["<hr/>\nBelow", "* * *\nBelow"], ["Above\n<hr/>", "Above\n* * *"], ["Above\n<hr/>\nBelow", "Above\n* * *\nBelow"],])]
  public function hr($expected, $input) {
    $this->assertMarkdown($expected, $input);
  }

  #[Test]
  public function fenced_code() {
    $this->assertMarkdown('<code indent="" lang="php">class Test { }</code>', "```php\nclass Test { }\n```");
  }

  #[Test]
  public function indented_fenced_code() {
    $this->assertMarkdown('<code indent="  " lang="sh">$ xp test</code>', "  ```sh\n  \$ xp test\n  ```");
  }

  #[Test, Values(['- Hello', '+ Hello', '* Hello'])]
  public function li($variation) {
    $this->assertMarkdown('<li symbol="'.$variation[0].'">Hello</li>', $variation);
  }

  #[Test]
  public function lis() {
    $this->assertMarkdown("<li symbol=\"-\">Hello</li>\n<li symbol=\"-\">World</li>", "- Hello\n- World");
  }
}