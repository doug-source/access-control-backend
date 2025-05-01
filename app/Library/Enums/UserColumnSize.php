<?php

declare(strict_types=1);

namespace App\Library\Enums;

enum UserColumnSize
{
    case EMAIL;

    public function get(): int
    {
        return match ($this) {
            UserColumnSize::EMAIL => 250
        };
    }
}
