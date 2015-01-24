<?hh namespace net\xp_framework\unittest\reflection;

<<action('Actionable')>>
class HackLanguageSupport extends \lang\Object {
  
  public function returnsString(): string { return 'Test'; }

  public function returnsNothing(int $param): void { }

  <<test, limit(1.0), expect(['class' => 'lang.IllegalArgumentExcepton', 'withMessage' => '/*Blam*/'] )>>
  public function testAnnotations() { }
}