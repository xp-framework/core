<?hh namespace net\xp_framework\unittest\reflection;

class HackLanguageSupport extends \lang\Object {
  public bool $typed= false;
  public $untyped;

  public function returnsString(): string { return 'Test'; }

  public function returnsNum(): num { return 1; }

  public function returnsArraykey(): arraykey { return 1; }

  public function returnsThis(): this { return $this; }

  public function returnsNoreturn(): noreturn { }

  public function returnsSelf(self $self): self { return $self; }

  public function returnsNothing(int $param): void { }

}
