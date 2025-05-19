<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\Builders\Response as ResponseBuilder;
use App\Services\Register\RegisterServiceInterface;
use App\Http\Requests\User\CheckRequest;
use App\Library\Converters\Phone as PhoneConverter;
use App\Models\User;
use App\Repositories\RegisterPermissionRepository;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly RegisterServiceInterface $registerService,
        private readonly RegisterPermissionRepository $permissionRepository
    ) {
        // ...
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CheckRequest $request)
    {
        $registerPermission = $this->permissionRepository->findByEmail($request->email);
        $this->permissionRepository->delete($registerPermission->id);

        $phone = PhoneConverter::clear($registerPermission->phone ?? $request->phone);
        $fields = [...$request->only(['name', 'email', 'password']), 'phone' => $phone];
        $user = User::create($fields);
        event(new Registered($user));
        // $this->applyDefaultUserRole($user);

        return ResponseBuilder::successJSON(
            status: Response::HTTP_CREATED
        );
    }
}
