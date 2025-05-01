<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\Builders\Response as ResponseBuilder;
use App\Services\Register\RegisterServiceInterface;
use App\Http\Requests\User\CheckRequest;
use App\Library\Converters\Phone as PhoneConverter;
use App\Models\RegisterPermission;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class UserController extends Controller
{
    public function __construct(private readonly RegisterServiceInterface $registerService)
    {
        // ...
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CheckRequest $request)
    {
        $registerPermission = RegisterPermission::where('email', $request->email)->first();
        $phone = PhoneConverter::clear($registerPermission->phone ?? $request->phone);
        $fields = [...$request->only(['name', 'email', 'password']), 'phone' => $phone];
        $user = User::create($fields);
        RegisterPermission::destroy($registerPermission->id);
        event(new Registered($user));
        // $this->applyDefaultUserRole($user);

        return ResponseBuilder::successJSON();
    }
}
