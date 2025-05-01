<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'phone',
        'token',
        'expiration_data',
    ];
}
