<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class MultipleExists implements ValidationRule
{
    /** @var array<array{table: string, column: string, msg: string}> */
    private array $references;

    private bool $positive;

    /**
     * @param array<array{table: string, column: string, msg: string}> $references
     */
    public function __construct(array $references, bool $positive = TRUE)
    {
        $this->references = $references;
        $this->positive = $positive;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->references as $reference) {
            ['table' => $table, 'column' => $column, 'msg' => $msg] = $reference;
            if (DB::table($table)->where($column, $value)->exists() !== $this->positive) {
                $fail($msg);
                break;
            }
        }
    }
}
