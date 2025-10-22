<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Services\Auth\Contracts\StorageServiceInterface;

class StorageService implements StorageServiceInterface
{
    public function makeResourcePathUrl(string $fileName): string
    {
        $host = request()->schemeAndHttpHost();
        return "{$host}/storage/app/{$fileName}";
    }
}
