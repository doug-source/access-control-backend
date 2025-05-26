<?php

declare(strict_types=1);

namespace App\Repositories\Traits;

use App\Models\Contracts\UiSummary;
use Illuminate\Pagination\LengthAwarePaginator;

trait PickUiProperty
{
    /**
     * Select ui property from each model into paginator
     */
    private function pickUiSummary(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return tap($paginator, function (LengthAwarePaginator $paginatedInstance) {
            return $paginatedInstance->getCollection()->transform(function (UiSummary $role) {
                return $role->ui;
            });
        });
    }
}
