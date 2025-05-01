<?php

declare(strict_types=1);

namespace App\Library\Converters;

use Illuminate\Support\Str;

final class Phone
{
    /**
     * Keep only digits from phone
     */
    public static function clear(?string $phone): ?string
    {
        if (!$phone) {
            return $phone;
        }
        return Str::of($phone)->replaceMatches('|[^\d]|', '')->toString();
    }

    /**
     * Remove all separators (whitespaces and hyphens) from phone
     */
    public static function chopSeparators(?string $phone): ?string
    {
        if (!$phone) {
            return $phone;
        }
        return Str::of($phone)->replaceMatches('|[\-\s]|', '')->toString();
    }
}
