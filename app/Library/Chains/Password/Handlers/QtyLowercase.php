<?php

declare(strict_types=1);

namespace App\Library\Chains\Password\Handlers;

use App\Library\Chains\Password\RuleHandler as PasswordRuleHandler;
use App\Library\Enums\PhraseKey;
use Illuminate\Support\Stringable;

class QtyLowercase extends PasswordRuleHandler
{
    public function __construct(int $base)
    {
        parent::__construct($base, PhraseKey::MinLowercaseInvalid, ": ($base)");
    }

    function validate(Stringable $value): bool
    {
        return $value->replaceMatches('|[^a-záàâãéèêíïóôõöúçñ]|', '')->length() < $this->base;
    }
}
