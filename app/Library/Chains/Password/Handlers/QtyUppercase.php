<?php

declare(strict_types=1);

namespace App\Library\Chains\Password\Handlers;

use App\Library\Chains\Password\RuleHandler as PasswordRuleHandler;
use App\Library\Enums\PhraseKey;
use Illuminate\Support\Stringable;

final class QtyUppercase extends PasswordRuleHandler
{
    public function __construct(int $base)
    {
        parent::__construct($base, PhraseKey::MinUppercaseInvalid, ": ($base)");
    }

    function validate(Stringable $value): bool
    {
        return $value->replaceMatches('|[^A-ZÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ]|', '')->length() < $this->base;
    }
}
