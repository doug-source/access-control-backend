<?php

namespace App\Http\Requests\Auth\Strategy\Post;

use App\Http\Requests\Checker;
use App\Rules\UserNotProvided;
use Illuminate\Foundation\Http\FormRequest;

class Login implements Checker
{
    /** @var string */
    private $email;

    public function __construct(FormRequest $formRequest)
    {
        $this->email = $formRequest->input('email');
    }

    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
        ];
    }

    public function rules(): array
    {
        $providers = config('services.providers');

        return [
            'email' => [
                'required',
                'email',
                new UserNotProvided($this->email, $providers)
            ],
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('email') . ': ' . __('required'),
            'email.email' => __('email') . ': ' . __('invalid'),
            'password.required' => __('password') . ': ' . __('required'),
        ];
    }
}
