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
use App\Repositories\RoleRepository;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Pagination\LengthAwarePaginator;

final class AbilityService implements AbilityServiceInterface
{
    public function __construct(
        private AbilityRepository $abilityRepository,
        private RoleRepository $roleRepository,
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
     * Group the "to include" abilities into role's abilities collection and
     * also remove those "to remove".
     *
     * @param array{included: Collection<int, Ability>, removed: Collection<int, Ability>} $filters
     * @param SupportCollection<int, Ability> $roleAbilities
     */
    private function manageAbilities(array $filters, SupportCollection $roleAbilities)
    {
        ['included' => $includeList, 'removed' => $removeList] = $filters;
        $grouped = $roleAbilities->concat($includeList);

        return $grouped->reject(function (Ability $ability) use (&$removeList) {
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
                results: $results->map(fn($ability) => $ability->ui),
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

    public function findReferenceRoleAbilities(Role $role, bool $owner, int $page, int $group, ?string $name = NULL): LengthAwarePaginator
    {
        if ($owner) {
            return $this->roleRepository->paginateAbilities(
                role: $role,
                page: $page,
                group: $group,
                name: $name,
            );
        }
        return $this->abilityRepository->paginate(
            page: $page,
            group: $group,
            name: $name,
            exclude: $role->abilities->pluck('id')->all(),
        );
    }
}
