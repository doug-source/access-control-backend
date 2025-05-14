<?php

declare(strict_types=1);

namespace App\Library\Chains\Password;

use Closure;
use Illuminate\Support\Stringable;

interface RuleHandlerInterface
{
    public function validate(Stringable $value): bool;
    public function setNext(self $next): self;
    /**
     * Handle the Rule
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function handle(Stringable $value, Closure $fail): void;
}
