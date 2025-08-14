<?php

declare(strict_types=1);

namespace App\Library\Enums;

enum PhraseKey
{
    case UserNullable;
    case PasswordNotNullable;
    case ProviderInvalid;
    case ProviderCredentialsInvalid;
    case LoginByProvider;
    case EmailInvalid;
    case LoginInvalid;
    case ParameterInvalid;
    case ParameterRequired;
    case MinSizeInvalid;
    case MaxSizeInvalid;
    case MinLettersInvalid;
    case MinUppercaseInvalid;
    case MinLowercaseInvalid;
    case MinDigitsInvalid;
    case MinSpecialCharsInvalid;
    case RegistrationExpired;
    case PassConfirmInvalid;
    case EmailAlreadyVerified;

    case Congratulations;
    case ClickHere;
    case PreRegisterUserTextOne;
    case PreRegisterUserTextTwo;
    case Regards;
    case Hello;
    case ConfirmationEmail;
    case ConfirmationEmailText;
    case RegisterApproval;

    case PasswordsSent;
    case PasswordsReset;
    case PasswordsThrottled;
    case PasswordsToken;
    case PasswordsUser;

    case MINUTES;
    case ForgotPasswordExpireLine;
    case ForgotPasswordOtherwiseLine;
    case ForgotPasswordTitle;
    case ForgotPasswordText;
    case ForgotPasswordAction;

    case LinkNotAllowed;
    case NameAlreadyUsed;

    case InvalidRoleRemotion;
    case InvalidRoleInclusion;

    case InvalidAbilityRemotion;
    case InvalidAbilityInclusion;

    case ValidMimes;
    case Or;

    case MaxFileSizeInvalid;
}
