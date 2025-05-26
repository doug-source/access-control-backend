<?php

namespace App\Models;

use App\Models\Contracts\UiSummary;
use App\Models\Traits\FormatDatetimeProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Role extends Model implements UiSummary
{
    use HasFactory, FormatDatetimeProperty;

    protected $fillable = [
        'name',
    ];

    /**
     * Relationship with database table users
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Relationship with database table abilities
     */
    public function abilities(): BelongsToMany
    {
        return $this->belongsToMany(Ability::class)->withTimestamps();
    }

    /**
     * Format the created_at to view
     *
     * @return string
     */
    public function getCreatedAtFormattedAttribute()
    {
        return $this->getPropertyFormatted('created_at');
    }

    /**
     * Format the updated_at to view
     *
     * @return string
     */
    public function getUpdatedAtFormattedAttribute()
    {
        return $this->getPropertyFormatted('updated_at');
    }

    /**
     * {@inheritDoc}
     *
     * @return array{id: string, name: string, createdAt: string, updatedAt: string}
     */
    public function getUiAttribute(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'createdAt' => $this->created_at_formatted,
            'updatedAt' => $this->updated_at_formatted,
        ];
    }
}
