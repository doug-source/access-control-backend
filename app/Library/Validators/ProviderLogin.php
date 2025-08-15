<?php

declare(strict_types=1);

namespace App\Library\Validators;

use App\Repositories\UserRepository;
use App\Rules\ProviderUserLinked;

final class ProviderLogin extends AbstractProvider
{
    protected UserRepository $userRepository;

    public function __construct($provider, UserRepository $userRepository)
    {
        parent::__construct($provider);
        $this->userRepository = $userRepository;
    }

    protected function rules(): array
    {
        $rules = parent::rules();
        return [
            ...$rules,
            new ProviderUserLinked(
                provider: $this->provider,
                userRepository: $this->userRepository
            )
        ];
    }
}
