<?php

namespace App\Models;

use App\Library\Converters\Phone as PhoneConverter;
use App\Models\Traits\FormatDatetimeProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterRequest extends Model
{
    use HasFactory, FormatDatetimeProperty;

    protected $fillable = [
        'email',
        'phone',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Sanitize the phone number column value
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = PhoneConverter::clear($value);
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
