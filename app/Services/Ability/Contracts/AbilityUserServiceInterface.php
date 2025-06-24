<?php

declare(strict_types=1);

namespace App\Services\Ability\Contracts;

use Illuminate\Support\Collection;

interface AbilityUserServiceInterface
{
    /**
     * @param \Illuminate\Support\Collection<int, string> $names
     * @param \Illuminate\Support\Collection<int, \App\Models\Ability> $inversedAbilities
     * @param bool $abilityStatus
     * @param \Illuminate\Support\Collection<int, array{include: bool}>|array<int, array{include: bool}> $acc
     * @return \Illuminate\Support\Collection<int, array{include:bool}>
     */
    public function organizeAbilities(Collection $names, Collection $inversedAbilities, bool $abilityStatus, array|Collection $acc = []): Collection;
}
