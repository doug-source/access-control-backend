<?php

declare(strict_types=1);

namespace App\Http\Requests\User\Strategy\Patch;

use App\Http\Requests\Checker;
use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\UserSize;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Number;
use App\Rules\PhoneValid;

final class Plain implements Checker
{
    private int $maxNameColumnSize;

    private int $phoneMaxSize;

    private array $mimes = ['jpeg', 'jpg', 'png'];

    /** @var int $fileSize Defined on killobytes */
    private int $fileSize = 1024;

    public function __construct()
    {
        $this->maxNameColumnSize = UserSize::NAME->get();
        $this->phoneMaxSize = UserSize::PHONE->get();
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
            'name' => [
                'bail',
                'required',
                "max:{$this->maxNameColumnSize}"
            ],
            'phone' => [
                'nullable',
                new PhoneValid($this->phoneMaxSize)
            ],
            'photo' => [
                'nullable',
                'mimes:jpeg,png',
                "max:{$this->fileSize}"
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => Phrase::pickSentence(PhraseKey::ParameterRequired),
            'name.max' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$this->maxNameColumnSize})"),

            'photo.mimes' => $this->buildMimeErrorMessage(),
            'photo.max' => $this->buildPhotoMaxErrorMessage(),
        ];
    }

    /**
     * Build the invalid MimeType error message
     */
    public function buildMimeErrorMessage()
    {
        return Phrase::pickSentence(
            PhraseKey::ValidMimes,
            Str::of(
                collect($this->mimes)->implode(', ')
            )->replaceLast(
                ', ',
                Phrase::pickSentence(key: PhraseKey::Or, uppercase: FALSE)->wrap(' ', ' ')
            )->wrap(' (', ')')->toString()
        );
    }

    /**
     * Build the invalid photo max size error message
     */
    public function buildPhotoMaxErrorMessage()
    {
        return Phrase::pickSentence(
            PhraseKey::MaxFileSizeInvalid,
            Str::of(
                Number::fileSize(bytes: $this->fileSize * 1024)
            )->replaceFirst(' ', '')->wrap(' (', ')')->toString()
        );
    }
}
