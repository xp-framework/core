<?hh namespace net\xp_framework\unittest\reflection;

class HackLanguageSupport extends \lang\Object {
  public bool $typed= false;
  public $untyped;

  public function returnsString(): string { return 'Test'; }

  public function returnsThis(): this { return $this; }

  public function returnsSelf(self $self): self { return $self; }

  public function returnsNothing(int $param): void { }

}
