<?php

declare(strict_types=1);

namespace App\Library\Chains\Password;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Support\Stringable;
use Illuminate\Support\Str;
use Closure;

abstract class RuleHandler implements RuleHandlerInterface
{
    protected ?RuleHandlerInterface $next;

    /**
     * Constructor of this class
     */
    public function __construct(
        protected readonly int $base,
        protected readonly PhraseKey $phraseKey,
        protected readonly string $msgRemain = '',
    ) {}

    public function setNext(?RuleHandlerInterface $next): RuleHandlerInterface
    {
        $this->next = $next;
        return $this;
    }

    public function handle(Stringable $value, Closure $fail): void
    {
        if ($this->base > -1 && $this->validate($value)) {
            $fail(
                Str::of(
                    Phrase::pickSentence($this->phraseKey)
                )->append($this->msgRemain)
            );
        } else if (!is_null($this->next)) {
            $this->next->handle($value, $fail);
        }
    }

    abstract function validate(Stringable $value): bool;
}
