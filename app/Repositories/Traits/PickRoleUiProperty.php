<?php

declare(strict_types=1);

namespace App\Repositories\Traits;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

trait PickRoleUiProperty
{
    /**
     * Select ui property from each Role model into paginator
     */
    private function pickRoleUi(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return tap($paginator, function (LengthAwarePaginator $paginatedInstance) {
            return $paginatedInstance->getCollection()->transform(function (Role $role) {
                return $role->ui;
            });
        });
    }
}
