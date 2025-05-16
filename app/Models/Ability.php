<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ability extends Model
{
    use HasFactory;

    /**
     * Relationship with database table users
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class
        )->withPivot('include')->withTimestamps()->using(AbilityUser::class);
    }

    /**
     * Relationship with database table roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
}
