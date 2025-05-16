<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AbilityUser extends Pivot
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'include' => 'boolean'
        ];
    }
}
