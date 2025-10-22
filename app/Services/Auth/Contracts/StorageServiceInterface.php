<?php

declare(strict_types=1);

namespace App\Services\Auth\Contracts;

interface StorageServiceInterface
{
    /**
     * Make the resource's path url
     */
    public function makeResourcePathUrl(string $fileName): string;
}
