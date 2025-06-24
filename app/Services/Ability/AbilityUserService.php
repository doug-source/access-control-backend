<?php

declare(strict_types=1);

namespace App\Services\Ability;

use App\Models\Ability;
use App\Repositories\AbilityRepository;
use App\Services\Ability\Contracts\AbilityUserServiceInterface;
use Illuminate\Support\Collection;

class AbilityUserService implements AbilityUserServiceInterface
{
    /**
     * Separate the inversedAbilities based on names collection
     *
     * @param \Illuminate\Support\Collection<int, string> $names
     * @param \Illuminate\Support\Collection<int, \App\Models\Ability> $inversedAbilities
     * @param bool $abilityStatus
     * @param \Illuminate\Support\Collection<int, array{include:bool}> $acc
     * @return array{\Illuminate\Support\Collection<int, array{include:bool}>, \Illuminate\Support\Collection<int, \App\Models\Ability>}
     */
    private function splitAbilities(Collection $names, Collection $inversedAbilities, bool $abilityStatus, Collection $acc): array
    {
        /** @var \Illuminate\Support\Collection<int, \App\Models\Ability> */
        $futureRemotion = collect();
        foreach ($names as $name) {
            $old = $inversedAbilities->first(fn(Ability $ability) => $ability->name === $name);
            if (is_null($old)) {
                $acc->put(
                    app(AbilityRepository::class)->findByName($name)->id,
                    ['include' => $abilityStatus]
                );
            } else {
                $futureRemotion->push($old);
            }
        }
        return [$acc, $futureRemotion];
    }

    public function organizeAbilities(
        Collection $names,
        Collection $inversedAbilities,
        bool $abilityStatus,
        array|Collection $acc = []
    ): Collection {
        [$acc, $futureRemotion] = $this->splitAbilities(
            $names,
            $inversedAbilities,
            $abilityStatus,
            collect($acc)
        );

        return $inversedAbilities->reject(
            fn(Ability $ability) => $futureRemotion->contains(fn(Ability $toRemove) => $ability->id === $toRemove->id)
        )->reduce(function ($carry, $ability) {
            $carry->put($ability->id, ['include' => $ability->pivot->include]);
            return $carry;
        }, $acc);
    }
}
