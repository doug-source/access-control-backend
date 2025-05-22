<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum RoleSize
{
    case NAME;

    public function get(): int
    {
        return match ($this) {
            RoleSize::NAME => 50,
        };
    }
}
