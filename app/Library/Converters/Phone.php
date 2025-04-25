<?php

declare(strict_types=1);

namespace App\Library\Converters;

final class Phone
{
    public static function clear(?string $phone): ?string
    {
        if (!$phone) {
            return $phone;
        }
        return trim(substr(preg_replace('|[^\d]|', '', $phone), 0, 11));
    }
}
