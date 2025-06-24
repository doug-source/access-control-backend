<?php

declare(strict_types=1);

namespace App\Services\Ability\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface AbilityUserServiceInterface
{
    /**
     * Execute the abilities handling used by synchronization between user and abilities (inclusion and remotion). At the end it generates the final output list at the pattern [ abilityId, ['include' => bool ] ]
     *
     * @param \Illuminate\Support\Collection<int, string> $names
     * @param \Illuminate\Support\Collection<int, \App\Models\Ability> $inversedAbilities
     * @param bool $abilityStatus
     * @param \Illuminate\Support\Collection<int, array{include: bool}>|array<int, array{include: bool}> $acc
     * @return \Illuminate\Support\Collection<int, array{include:bool}>
     */
    public function organizeAbilities(Collection $names, Collection $inversedAbilities, bool $abilityStatus, array|Collection $acc = []): Collection;

    /**
     * Pick the abilities/user with relation 'include' equal to TRUE
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Ability>
     */
    public function abilitiesIncludedFromUser(User $user): Collection;

    /**
     * Pick the abilities/user with relation 'include' equal to FALSE
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Ability>
     */
    public function abilitiesRemovedFromUser(User $user): Collection;
}
