<?php

namespace App\Http\Requests\User\Strategy\Post;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\ColumnSize\RegisterPermissionSize;
use App\Repositories\RegisterPermissionRepository;
use App\Rules\RegisterPermissionValid;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

final class Plain extends AbstractPost
{
    private RegisterPermissionRepository $permissionRepository;
    private int $tokenMaxSize;
    private ?string $token = NULL;

    public function __construct(
        FormRequest $formRequest,
        RegisterPermissionRepository $permissionRepository,
    ) {
        parent::__construct($formRequest);
        $this->permissionRepository = $permissionRepository;
        $this->tokenMaxSize = RegisterPermissionSize::TOKEN->get();
        $this->token = $formRequest->input('token');
    }

    /**
     * Attach the 'exists' validation rule to validate email parameter
     */
    private function attachPermitionEmailExists(Collection $rules): Collection
    {
        $emailRule = collect($rules->get('email'))->push('exists:register_permissions,email');
        return $rules->merge(['email' => $emailRule->all()]);
    }

    public function rules(): array
    {
        return $this->attachPermitionEmailExists(collect(parent::rules()))->put('token', [
            'bail',
            'required',
            "max:{$this->tokenMaxSize}",
            new RegisterPermissionValid(
                allowed: $this->permissionRepository->findByEmail($this->email),
                token: $this->token,
            )
        ])->all();
    }

    public function messages(): array
    {
        return collect(parent::messages())->merge([
            'email.exists' => Phrase::pickSentence(PhraseKey::EmailInvalid),
            'token.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'token.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->tokenMaxSize})"),
        ])->all();
    }
}
