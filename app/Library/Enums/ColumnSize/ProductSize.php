<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum ProductSize
{
    case NAME;
    case DESCRIPTION;

    public function get(): int
    {
        return match ($this) {
            ProductSize::NAME,
            ProductSize::DESCRIPTION => 255,
        };
    }
}
