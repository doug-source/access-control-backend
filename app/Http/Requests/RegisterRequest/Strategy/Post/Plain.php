<?php

declare(strict_types=1);

namespace App\Http\Requests\RegisterRequest\Strategy\Post;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\RegisterRequestColumnSize;
use App\Rules\PhoneValid;
use Illuminate\Foundation\Http\FormRequest;

class Plain implements Checker
{
    private $emailMaxSize;
    private $phoneMaxSize;

    public function __construct()
    {
        $this->emailMaxSize = RegisterRequestColumnSize::EMAIL->get();
        $this->phoneMaxSize = RegisterRequestColumnSize::PHONE->get();
    }

    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
        ];
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                "max:{$this->emailMaxSize}",
            ],
            'phone' => [
                'nullable',
                new PhoneValid($this->phoneMaxSize)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'email.email' => Phrase::pickSentence(PhraseKey::EmailInvalid),
            'email.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->emailMaxSize})"),
        ];
    }
}
