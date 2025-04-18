<?php

namespace App\Http\Requests\Auth\Strategy\Post;

use App\Http\Requests\Checker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class Login implements Checker
{
    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
        ];
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('email') . ': ' . __('required'),
            'email.email' => __('email') . ': ' . __('invalid'),
            'password.required' => __('password') . ': ' . __('required')
        ];
    }
}
