<?php

declare(strict_types=1);

namespace App\Library\Enums;

enum PhraseKey
{
    case UserNullable;
    case PasswordNotNullable;
    case ProviderInvalid;
    case LoginByProvider;
    case EmailInvalid;
    case LoginInvalid;
    case ParameterInvalid;
    case ParameterRequired;
    case MinSizeInvalid;
    case MaxSizeInvalid;

    case Congratulations;
    case ClickHere;
    case PreRegisterUserTextOne;
    case PreRegisterUserTextTwo;
    case Regards;
    case RegisterApproval;
}
