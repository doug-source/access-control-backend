<?php

declare(strict_types=1);

namespace App\Http\Requests\EmailVerify\Strategy\Post;

use App\Http\Requests\Checker;
use App\Rules\EmailVerifyResendValid;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;

final class Resend implements Checker
{
    private Authenticatable $user;

    public function __construct()
    {
        $this->user = Auth::user();
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
                new EmailVerifyResendValid()
            ]
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
