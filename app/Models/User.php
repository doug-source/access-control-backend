<?php

namespace App\Models;

use App\Models\Contracts\UiSummary;
use App\Models\Traits\FormatDatetimeProperty;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

final class User extends Authenticatable implements MustVerifyEmail, UiSummary
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, FormatDatetimeProperty, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
        ];
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
     * Format the email_verified_at to view
     *
     * @return string
     */
    public function getEmailVerifiedAtFormattedAttribute()
    {
        return $this->getPropertyFormatted('email_verified_at');
    }

    /**
     * {@inheritDoc}
     *
     * @return array{id: string, name: string, email: string, createdAt: string, updatedAt: string}
     */
    public function getUiAttribute(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'emailVerifiedAt' => $this->email_verified_at_formatted,
            'createdAt' => $this->created_at_formatted,
            'updatedAt' => $this->updated_at_formatted,
        ];
    }


    /**
     * Relationship between users and providers tables
     */
    public function providers()
    {
        return $this->hasMany(Provider::class, 'user_id', 'id');
    }

    /**
     * Relationship with database table abilities
     */
    public function abilities(): BelongsToMany
    {
        return $this->belongsToMany(
            Ability::class
        )->withPivot('include')->withTimestamps()->using(AbilityUser::class);
    }

    /**
     * Relationship with database table roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Define if this instance has super-admin role
     */
    public function isSuperAdmin()
    {
        return $this->roles()->where('name', 'super-admin')->exists() === TRUE;
    }
}
