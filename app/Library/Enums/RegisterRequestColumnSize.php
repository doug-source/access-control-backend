<?php

declare(strict_types=1);

namespace App\Library\Enums;

enum RegisterRequestColumnSize
{
    case EMAIL;
    case PHONE;

    public function get(): int
    {
        return match ($this) {
            RegisterRequestColumnSize::EMAIL => 250,
            RegisterRequestColumnSize::PHONE => 11
        };
    }
}
