<?hh namespace net\xp_framework\unittest\reflection;

class HackLanguageSupport extends \lang\Object {
  
  public function returnsString(): string { return 'Test'; }

  public function returnsNothing(int $param): void { }
}