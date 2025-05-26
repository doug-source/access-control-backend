<?php

declare(strict_types=1);

namespace App\Models\Contracts;

interface UiSummary
{
    /**
     * Format the summarized fields to view
     */
    public function getUiAttribute(): array;
}
