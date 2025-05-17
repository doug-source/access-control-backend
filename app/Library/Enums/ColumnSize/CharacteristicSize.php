<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum CharacteristicSize
{
    case NAME;

    public function get(): int
    {
        return match ($this) {
            CharacteristicSize::NAME => 100,
        };
    }
}
