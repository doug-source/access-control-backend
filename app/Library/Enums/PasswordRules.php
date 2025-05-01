<?php

namespace App\Library\Enums;

enum PasswordRules
{
    case QtySpecialChars;
    case QtyDigits;
    case QtyLetters;
    case QtyUppercase;
    case QtyLowercase;
    case MinSize;

    public function get(): int
    {
        return match ($this) {
            PasswordRules::QtySpecialChars,
            PasswordRules::QtyDigits,
            PasswordRules::QtyLetters,
            PasswordRules::QtyUppercase,
            PasswordRules::QtyLowercase => 1,
            PasswordRules::MinSize => 8
        };
    }
}
