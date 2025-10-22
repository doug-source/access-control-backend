<?php

declare(strict_types=1);

namespace App\Library\Builders;

use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;

final class Pagination
{

    public static function paginate(Collection $results, int $page, int $group)
    {
        return self::paginator(
            items: $results->forPage($page, $group)->values(),
            total: $results->count(),
            page: $page,
            group: $group,
            options: ['path' => '/', 'pageName' => 'page']
        );
    }

    /**
     * Create a new length-aware paginator instance
     */
    private static function paginator(Collection $items, int $total, int $page, int $group, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, [
            'items' => $items,
            'total' => $total,
            'perPage' => $group,
            'currentPage' => $page,
            'options' => $options,
        ]);
    }
}
