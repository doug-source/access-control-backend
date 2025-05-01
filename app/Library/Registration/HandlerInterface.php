<?php

declare(strict_types=1);

namespace App\Library\Registration;

interface HandlerInterface
{
    public function handle(string $email, ?string $phone): bool;
}
