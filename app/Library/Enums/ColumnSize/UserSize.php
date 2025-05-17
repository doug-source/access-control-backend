<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum UserSize
{
    case EMAIL;
    case NAME;
    case PHONE;

    public function get(): int
    {
        return match ($this) {
            UserSize::EMAIL,
            UserSize::NAME => 250,
            UserSize::PHONE => 11
        };
    }
}
