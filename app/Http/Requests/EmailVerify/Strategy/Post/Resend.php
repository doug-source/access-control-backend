<?php

declare(strict_types=1);

namespace App\Http\Requests\EmailVerify\Strategy\Post;

use App\Http\Requests\Checker;
use App\Repositories\UserRepository;
use App\Rules\EmailVerifyResendValid;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;

final class Resend implements Checker
{
    private Authenticatable $user;

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->user = Auth::user();
        $this->userRepository = $userRepository;
    }

    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
            'status' => $this->user->id
        ];
    }

    public function rules(): array
    {
        return [
            'status' => [
                new EmailVerifyResendValid(userRepository: $this->userRepository)
            ]
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
