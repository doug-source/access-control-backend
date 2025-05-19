<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Model;

    public function create(array $attributes = []): ?Model;

    public function delete(int $id): int;

    public function update(int $id, array $attributes = []): int;

    public function loadModel(): Model;
}
