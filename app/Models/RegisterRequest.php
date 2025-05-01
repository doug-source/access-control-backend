<?php

namespace App\Models;

use App\Library\Converters\Phone as PhoneConverter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'phone',
    ];

    /**
     * Sanitize the phone number column value
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = PhoneConverter::clear($value);
    }
}
