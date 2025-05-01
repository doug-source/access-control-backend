<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

interface CheckerFactoryScheme
{
    /**
     * Return the Checker instance based on FormRequest instance
     */
    public function getChecker(FormRequest $formRequest): ?Checker;
}
