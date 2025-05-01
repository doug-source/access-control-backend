<?php

declare(strict_types=1);

namespace App\Library\Builders;

final class Token
{
    public static function build(): string
    {
        return bin2hex(random_bytes(20));
    }
}
