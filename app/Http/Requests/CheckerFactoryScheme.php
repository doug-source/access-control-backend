<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

interface CheckerFactoryScheme
{
    function getChecker(FormRequest $formRequest): ?Checker;
}
