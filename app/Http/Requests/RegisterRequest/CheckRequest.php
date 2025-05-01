<?php

declare(strict_types=1);

namespace App\Http\Requests\RegisterRequest;

use App\Http\Requests\VerifyRequest;
use App\Http\Requests\RegisterRequest\Strategy\CheckerFactory;
use Illuminate\Http\Request;

final class CheckRequest extends VerifyRequest
{
    public function __construct()
    {
        parent::__construct(new CheckerFactory());
    }

    public function authorize(): bool
    {
        $method = strtolower(Request::method());
        if ($method === 'post') {
            return $this->isLoggedIn() === FALSE;
        }
        if ($method === 'delete') {
            return $this->isLoggedIn();
        }
        return $this->isLoggedIn();
    }
}
