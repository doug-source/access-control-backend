<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum RegisterRequestSize
{
    case EMAIL;
    case PHONE;

    public function get(): int
    {
        return match ($this) {
            RegisterRequestSize::EMAIL => 250,
            RegisterRequestSize::PHONE => 11
        };
    }
}
