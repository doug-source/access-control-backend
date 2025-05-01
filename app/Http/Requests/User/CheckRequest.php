<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\VerifyRequest;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\User\Strategy\CheckerFactory;
use Illuminate\Support\Uri;

final class CheckRequest extends VerifyRequest
{
    public function __construct()
    {
        parent::__construct(new CheckerFactory());
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
