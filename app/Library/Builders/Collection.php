<?php

declare(strict_types=1);

namespace App\Library\Builders;

use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class Collection
{
    /**
     * Receive the list of names to remove from collection
     *
     * @template T
     * @param \Illuminate\Database\Eloquent\Collection<int, T> $list
     * @param string|array<string>|\Illuminate\Support\Collection<int, string> $namesToRemove
     * @return \Illuminate\Database\Eloquent\Collection<int, T>
     */
    public static function rejectByName(EloquentCollection $list, string|array|BaseCollection $namesToRemove): EloquentCollection
    {
        $namesToRemove = collect($namesToRemove);

        return $list->reject(
            fn($instance) => $namesToRemove->contains(
                fn(string $name) => $name === $instance->name
            )
        );
    }
}
