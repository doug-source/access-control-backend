<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum UserSize
{
    case EMAIL;
    case NAME;
    case PHONE;
    case PASSWORD;
    case PHOTO;

    public function get(): int
    {
        return match ($this) {
            UserSize::EMAIL,
            UserSize::PASSWORD,
            UserSize::NAME => 255,
            UserSize::PHOTO => 100,
            UserSize::PHONE => 11,
        };
    }
}
