<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Ability;
use Illuminate\Pagination\LengthAwarePaginator;

class AbilityRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(Ability::class);
    }

    /**
     * Query the Ability instance pagination list
     */
    public function paginate(int $page, int $group, ?string $name = NULL, array $exclude = []): LengthAwarePaginator
    {
        $query = $this->loadModel()::query()->select('id', 'name')->whereNotIn('id', $exclude);
        if ($name) {
            $query = $query->where([
                ['name', 'like', "%{$name}%"]
            ]);
        }
        return $query->paginate(
            page: $page,
            perPage: $group,
            columns: ['id', 'name']
        );
    }

    /**
     * Search an Ability instance by name
     */
    public function findByName(?string $name): ?Ability
    {
        if (is_null($name)) {
            return NULL;
        }
        return $this->loadModel()::query()->firstWhere('name', $name);
    }
}
