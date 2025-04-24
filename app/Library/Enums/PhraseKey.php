<?php

declare(strict_types=1);

namespace App\Library\Enums;

enum PhraseKey
{
    case UserNullable;
    case PasswordRequired;
    case PasswordNotNullable;
    case ProviderInvalid;
    case LoginByProvider;
    case EmailRequired;
    case EmailInvalid;
    case LoginInvalid;
}
