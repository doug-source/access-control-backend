<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Provider;

class ProviderRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Provider::class);
    }
}
