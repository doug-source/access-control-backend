<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CheckRequest;
use App\Library\Converters\Phone as PhoneConverter;
use App\Library\Builders\Response as ResponseBuilder;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

class FastUserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly UserRepository $userRepository)
    {
        // ...
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CheckRequest $request)
    {
        $this->authorize('create', User::class);
        $phone = PhoneConverter::clear($request->phone);
        $user = $this->userRepository->create(
            attributes: [
                ...$request->only(['name', 'email', 'password', 'phone']),
                'phone' => $phone
            ]
        );

        return ResponseBuilder::successJSON(
            status: Response::HTTP_CREATED,
            headers: [
                'Location' => route('user.show', ['user' => $user->id])
            ]
        );
    }
}
