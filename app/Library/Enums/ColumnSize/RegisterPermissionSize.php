<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum RegisterPermissionSize
{
    case TOKEN;
    case PHONE;

    public function get(): int
    {
        return match ($this) {
            RegisterPermissionSize::TOKEN => 40,
            RegisterPermissionSize::PHONE => 11
        };
    }
}
