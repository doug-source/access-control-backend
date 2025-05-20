<?php

declare(strict_types=1);

namespace App\Services\User\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface AbilityServiceInterface
{
    /**
     * Filter the available abilities from an user
     */
    public function abilitiesFromUser(User $user): Collection;
}
