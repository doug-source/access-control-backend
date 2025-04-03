<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

interface Checker
{
    public function all(FormRequest $formRequest, array $requestInputs): array;

    /**
     * ...
     *
     * @return array<string, mixed>
     */
    public function rules(): array;

    /**
     * ...
     *
     * @return array<string, mixed>
     */
    public function messages(): array;
}
