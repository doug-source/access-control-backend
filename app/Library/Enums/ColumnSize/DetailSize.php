<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum DetailSize
{
    case TYPE;

    public function get(): int
    {
        return match ($this) {
            DetailSize::TYPE => 255,
        };
    }
}
