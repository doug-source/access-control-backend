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
    public function paginate($perPage = 3, ?string $name = NULL): LengthAwarePaginator
    {
        $query = $this->loadModel()::query();
        if ($name) {
            $query = $query->where([
                ['name', 'like', "%{$name}%"]
            ]);
        }
        return tap($query->paginate(
            perPage: $perPage,
            columns: ['id', 'name', 'created_at', 'updated_at']
        ), function (LengthAwarePaginator $paginatedInstance) {
            return $paginatedInstance->getCollection()->transform(function (Ability $ability) {
                return $ability->ui;
            });
        });
    }
}
