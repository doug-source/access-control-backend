<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

trait IdLinkable
{
    /**
     * Return the User instance based on the user route parameter
     */
    private function buildRouteParam(FormRequest $formRequest, AbstractRepository $repository, string $routeKey): Model
    {
        $model = $repository->find(
            (int) $formRequest->route($routeKey)
        );
        if (is_null($model)) {
            abort(Response::HTTP_NOT_FOUND, 'Not Found');
        }
        return $model;
    }
}
