<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum InventorySize
{
    case IMAGE;

    public function get(): int
    {
        return match ($this) {
            InventorySize::IMAGE => 100,
        };
    }
}
