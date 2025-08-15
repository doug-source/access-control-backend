<?php

declare(strict_types=1);

namespace App\Library\Validators;

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;

class ProviderRequest extends AbstractProvider
{
    protected string $email;

    public function __construct($provider, string $email)
    {
        parent::__construct($provider);
        $this->email = $email;
    }

    protected function fields(): array
    {
        $fields = parent::fields();
        return [
            ...$fields,
            'email' => $this->email,
        ];
    }

    protected function rules(): array
    {
        $rules = parent::rules();
        return [
            ...$rules,
            'email' => 'email',
        ];
    }

    protected function messages(): array
    {
        $messages = parent::messages();
        return [
            ...$messages,
            'email.email' => Phrase::pickSentence(PhraseKey::EmailInvalid),
        ];
    }
}
