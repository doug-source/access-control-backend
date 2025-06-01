<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\VerifyRequest;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\User\Strategy\CheckerFactory;
use App\Repositories\RegisterPermissionRepository;
use Exception;
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
        switch ($method) {
            case 'get':
                return match ('/' . Uri::of(Request::getUri())->path()) {
                    route(name: 'user.create', absolute: false) => !$this->isLoggedIn(),
                    route(name: 'user.index', absolute: false) => $this->isLoggedIn(),
                    route(name: 'user.removed.index', absolute: false) => $this->isLoggedIn(),
                    default => throw new Exception('Validation not implemented', 1),
                };
            case 'post':
                return match ('/' . Uri::of(Request::getUri())->path()) {
                    route(name: 'users.store', absolute: false) => !$this->isLoggedIn(),
                    route(name: 'user.restore', absolute: false) => $this->isLoggedIn(),
                    default => throw new Exception('Validation not implemented', 1),
                };
            default:
                throw new Exception('Validation not implemented', 1);
        }
    }
}
