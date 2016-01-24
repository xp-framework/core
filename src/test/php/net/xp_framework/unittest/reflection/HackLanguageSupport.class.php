<?hh namespace net\xp_framework\unittest\reflection;

class HackLanguageSupport extends \lang\Object {
  public bool $typed= false;
  public $untyped;

  public function returnsString(): string { return 'Test'; }

  public function returnsNothing(int $param): void { }

}
