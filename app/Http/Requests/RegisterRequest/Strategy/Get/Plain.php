<?php

declare(strict_types=1);

namespace App\Http\Requests\RegisterRequest\Strategy\Get;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\RegisterRequestSize;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Used by RegisterRequest index
 */
final class Plain implements Checker
{
    private int $emailColumnSize;

    public function __construct()
    {
        $this->emailColumnSize = RegisterRequestSize::EMAIL->get();
    }

    public function all(FormRequest $formRequest, array $requestInputs): array
    {
        return [
            ...$requestInputs,
            'page' => $formRequest->query('page'),
            'group' => $formRequest->query('group'),
            'email' => $formRequest->query('email'),
        ];
    }

    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'group' => 'nullable|integer|min:1',
            'email' => "nullable|max:{$this->emailColumnSize}"
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'page.min' => Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)"),
            'group.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'group.min' => Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)"),
            'email.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->emailColumnSize})")
        ];
    }
}
