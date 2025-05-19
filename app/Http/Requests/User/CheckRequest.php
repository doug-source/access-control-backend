<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\VerifyRequest;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\User\Strategy\CheckerFactory;
use App\Repositories\RegisterPermissionRepository;
use Illuminate\Support\Uri;

final class CheckRequest extends VerifyRequest
{
    public function __construct(RegisterPermissionRepository $permissionRepository)
    {
        parent::__construct(
            factory: new CheckerFactory($permissionRepository),
        );
    }

    public function authorize(): bool
    {

        $method = strtolower(Request::method());
        $usersCreatePath = Uri::of(route('users.create'))->path();

        if (
            $method === 'get' &&
            Request::getPathInfo() === "/{$usersCreatePath}"
        ) {
            return !$this->isLoggedIn();
        }
        return !$this->isLoggedIn();
    }
}
