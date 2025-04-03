<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
            'password' => 'hashed',
        ];
    }

    /**
     * Format datetime properties based on current locale
     *
     * @return string
     */
    protected function getPropertyFormatted(string $prop)
    {
        $locale = App::getLocale();
        if ($locale === 'pt_BR') {
            return $this->$prop->format('d/m/Y');
        }
        return $this->$prop->format('m/d/Y');
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
}
