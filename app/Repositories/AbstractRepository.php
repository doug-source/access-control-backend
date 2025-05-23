<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class AbstractRepository implements RepositoryInterface
{
    protected string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * Get all of the models from the database.
     */
    public function all(): Collection
    {
        return $this->loadModel()::all();
    }

    /**
     * Find a model by its primary key.
     */
    public function find(int $id): ?Model
    {
        return $this->loadModel()::query()->find($id);
    }

    /**
     * Save a new model and return the instance.
     */
    public function create(array $attributes = []): ?Model
    {
        return $this->loadModel()::query()->create($attributes);
    }

    /**
     * Delete records from the database.
     */
    public function delete(int $id): int
    {
        return $this->loadModel()::query()->where('id', $id)->delete();
    }

    /**
     * Update records in the database.
     */
    public function update(int $id, array $attributes = []): int
    {
        return $this->loadModel()::query()->where('id', $id)->update($attributes);
    }

    /**
     * Get the available model instance.
     */
    public function loadModel(): Model
    {
        return app($this->model);
    }
}
