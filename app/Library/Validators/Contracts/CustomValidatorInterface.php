<?php

declare(strict_types=1);

namespace App\Library\Validators\Contracts;

interface CustomValidatorInterface
{
    /**
     * Execute the validation
     *
     * @return array<string, string>|array
     */
    public function validate(): array;
}
