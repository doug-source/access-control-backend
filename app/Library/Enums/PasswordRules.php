<?php

namespace App\Library\Enums;

use App\Library\Enums\ColumnSize\UserSize;

enum PasswordRules
{
    case QtySpecialChars;
    case QtyDigits;
    case QtyLetters;
    case QtyUppercase;
    case QtyLowercase;
    case MinSize;
    case MaxSize;

    public function get(): int
    {
        return match ($this) {
            PasswordRules::QtySpecialChars,
            PasswordRules::QtyDigits,
            PasswordRules::QtyLetters,
            PasswordRules::QtyUppercase,
            PasswordRules::QtyLowercase => 1,
            PasswordRules::MinSize => 8,
            PasswordRules::MaxSize => UserSize::PASSWORD->get(),
        };
    }
}
