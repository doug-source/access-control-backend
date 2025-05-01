<?php

declare(strict_types=1);

namespace App\Http\Requests\RegisterRequest\Strategy\Get;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\UserColumnSize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Used by RegisterRequest index
 */
final class Plain implements Checker
{
    private int $emailColumnSize;

    public function __construct()
    {
        $this->emailColumnSize = UserColumnSize::EMAIL->get();
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
            'page' => 'required|integer|min:1',
            'group' => 'required|integer|min:1',
            'email' => "nullable|max:{$this->emailColumnSize}"
        ];
    }

    public function messages(): array
    {
        return [
            'page.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'page.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'page.min' => Str::of(Phrase::pickSentence(PhraseKey::MinSizeInvalid))->append(" (1)"),
            'group.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'group.integer' => Phrase::pickSentence(PhraseKey::ParameterInvalid),
            'group.min' => Str::of(Phrase::pickSentence(PhraseKey::MinSizeInvalid))->append(" (1)"),
            'email.max' => Str::of(Phrase::pickSentence(PhraseKey::MaxSizeInvalid))->append(" ({$this->emailColumnSize})")
        ];
    }
}
