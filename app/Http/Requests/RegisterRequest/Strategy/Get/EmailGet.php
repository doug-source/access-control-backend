<?php

declare(strict_types=1);

namespace App\Http\Requests\RegisterRequest\Strategy\Get;

use App\Http\Requests\Shared\Strategies\Get;
use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\RegisterRequestSize;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Used by RegisterRequest index
 */
final class EmailGet extends Get
{
    private int $emailColumnSize;

    public function __construct()
    {
        $this->emailColumnSize = RegisterRequestSize::EMAIL->get();
    }

    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...parent::all($formRequest, $requestInputs),
            'email' => $formRequest->query('email'),
        ];
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'email' => "nullable|max:{$this->emailColumnSize}"
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'email.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->emailColumnSize})")
        ];
    }
}
