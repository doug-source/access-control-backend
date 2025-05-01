<?php

declare(strict_types=1);

namespace App\Library\Enums;

enum RegisterPermissionColumnSize
{
    case TOKEN;
    case PHONE;

    public function get(): int
    {
        return match ($this) {
            RegisterPermissionColumnSize::TOKEN => 40,
            RegisterPermissionColumnSize::PHONE => 11
        };
    }
}
