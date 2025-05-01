<?php

declare(strict_types=1);

namespace App\Library\Chains\Password\Handlers;

use App\Library\Chains\Password\RuleHandler as PasswordRuleHandler;
use App\Library\Enums\PhraseKey;
use Illuminate\Support\Stringable;

class QtyDigits extends PasswordRuleHandler
{
    public function __construct(int $base)
    {
        parent::__construct($base, PhraseKey::MinDigitsInvalid, ": ($base)");
    }

    function validate(Stringable $value): bool
    {
        return $value->replaceMatches('|[^0-9]|', '')->length() < $this->base;
    }
}
