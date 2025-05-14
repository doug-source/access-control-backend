<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Closure;
use Illuminate\Support\Collection;
use App\Library\Chains\Password\RuleHandlerInterface;

final class PasswordValid implements ValidationRule
{
    private Collection $ruleHandlers;

    /**
     * Constructor of this PasswordValid
     *
     * @param array<int, RuleHandlerInterface> $ruleHandlers
     */
    public function __construct(RuleHandlerInterface ...$ruleHandlers)
    {
        $this->ruleHandlers = collect($ruleHandlers);
        $this->ruleHandlers->each(
            fn($rule, $key) => $rule->setNext($this->ruleHandlers->get($key + 1))
        );
    }

    /**
     * {@inheritDoc}
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = Str::of($value)->trim();
        $this->ruleHandlers->first()->handle($value, $fail);
    }
}
