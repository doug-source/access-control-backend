<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Library\Builders\Pagination as PaginationBuilder;
use App\Models\Role;
use App\Services\User\Contracts\AbilityServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ability;
use App\Repositories\AbilityRepository;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Pagination\LengthAwarePaginator;

final class AbilityService implements AbilityServiceInterface
{
    public function __construct(
        private AbilityRepository $abilityRepository,
    ) {
        // ...
    }

    public function abilitiesFromUser(User $user): SupportCollection
    {
        return $this->manageAbilities(
            filters: $this->collectSingleAbilities($user),
            roleAbilities: $this->uniqueAbilities(
                $this->dettachRoleAbilities(
                    $user->roles->map(function (Role $role) {
                        return $role->abilities;
                    })
                )
            )
        );
    }

    /**
     * Merge the "to include" abilities into role's abilities collection and
     * also remove those "to remove".
     *
     * @param array{included: Collection<int, Ability>, removed: Collection<int, Ability>} $filters
     * @param SupportCollection<int, Ability> $roleAbilities
     */
    private function manageAbilities(array $filters, SupportCollection $roleAbilities)
    {
        ['included' => $includeList, 'removed' => $removeList] = $filters;
        $merged = $roleAbilities->merge($includeList->toArray());

        return $merged->reject(function (Ability $ability) use (&$removeList) {
            return $removeList->contains(function (Ability $removed) use (&$ability) {
                return $removed->id === $ability->id;
            });
        });
    }

    /**
     * Search by abilities linked directly to user into database, splitting them in
     * "to include" and "to remove" partitions
     *
     * @return array{included: Collection<int, Ability>, removed: Collection<int, Ability>}
     */
    private function collectSingleAbilities(User $user)
    {
        [$includeList, $removeList] = $user->abilities->partition(fn(Ability $ability) => $ability->pivot->include);
        return [
            'included' => $includeList,
            'removed' => $removeList,
        ];
    }

    /**
     * Return only unique abilities from the collection.
     *
     * @param SupportCollection<int, Ability> $abilities
     * @return SupportCollection<int, Ability>
     */
    private function uniqueAbilities(SupportCollection $abilities): SupportCollection
    {
        return collect($abilities)->unique('name');
    }

    /**
     * Get a flattened array of the abilities in the collection in one level depth
     *
     * @param SupportCollection<int, Ability> $abilities
     * @return SupportCollection<int, Ability>
     */
    private function dettachRoleAbilities(SupportCollection $abilities): SupportCollection
    {
        return $abilities->flatten(1);
    }

    public function findReferenceUserAbilities(User $user, bool $owner, int $page, int $group, ?string $name = NULL): LengthAwarePaginator
    {
        $results = $this->abilitiesFromUser($user);
        if ($owner) {
            return PaginationBuilder::paginate(
                results: $results,
                page: $page,
                group: $group,
            );
        }
        return $this->abilityRepository->paginate(
            page: $page,
            group: $group,
            name: $name,
            exclude: $results->pluck('id')->all(),
        );
    }
}
