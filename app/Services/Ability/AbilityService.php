<?php

declare(strict_types=1);

namespace App\Services\Ability;

use App\Library\Builders\Pagination as PaginationBuilder;
use App\Models\Role;
use App\Services\Ability\Contracts\AbilityServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Ability;
use App\Repositories\AbilityRepository;
use App\Repositories\RoleRepository;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Library\Builders\Collection as CollectionBuilder;

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
            roleAbilities: $this->abilitiesFromUserRoles($user)
        );
    }

    private function manageAbilities(array $filters, SupportCollection $roleAbilities)
    {
        ['included' => $includeList, 'removed' => $removeList] = $filters;
        $grouped = $roleAbilities->concat($includeList);

        return $grouped->reject(function (Ability $ability) use (&$removeList) {
            return $removeList->contains(function (Ability $removed) use (&$ability) {
                return $removed->id === $ability->id;
            });
        })->values();
    }

    /**
     * Search by abilities linked directly to user into database, splitting them in
     * "to include" and "to remove" partitions
     *
     * @return array{included: Collection<int, Ability>, removed: Collection<int, Ability>}
     */
    public function collectSingleAbilities(User $user): array
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

    public function combine(Collection $abilities, SupportCollection $namesToRemove, SupportCollection $namesToInclude): Collection
    {
        return CollectionBuilder::rejectByName(list: $abilities, namesToRemove: $namesToRemove)->concat(
            $this->abilityRepository->findByNames($namesToInclude)->all()
        );
    }

    public function abilitiesFromRoles(SupportCollection $collection): SupportCollection
    {
        return $collection->map(
            fn($role) => $role->abilities()->select('id', 'name')->get()
        );
    }

    public function abilitiesFromUserRoles(User $user): SupportCollection
    {
        return $this->uniqueAbilities(
            $this->dettachRoleAbilities(
                $this->abilitiesFromRoles($user->roles)
            )
        );
    }
}
