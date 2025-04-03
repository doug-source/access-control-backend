<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

interface Checker
{
    /**
     * Get all of the input and files for the request and organize the fields
     * to be validated.
     */
    public function all(FormRequest $formRequest, array $requestInputs): array;

    /**
     * Define the rules used by this checker in request validation
     */
    public function rules(): array;

    /**
     * Define the messages used by this checker in request invalidation
     */
    public function messages(): array;
}
