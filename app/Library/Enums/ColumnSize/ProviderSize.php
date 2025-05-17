<?php

declare(strict_types=1);

namespace App\Library\Enums\ColumnSize;

enum ProviderSize
{
    case PROVIDER;
    case PROVIDER_ID;
    case AVATAR;

    public function get(): int
    {
        return match ($this) {
            ProviderSize::PROVIDER,
            ProviderSize::PROVIDER_ID,
            ProviderSize::AVATAR => 255,
        };
    }
}
