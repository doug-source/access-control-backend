<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum AbilitySize
{
    case NAME;

    public function get(): int
    {
        return match ($this) {
            AbilitySize::NAME => 50,
        };
    }
}
