<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Superadmin extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'recoverable_password'];

    protected $hidden = ['password', 'recoverable_password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'             => 'hashed',
            'recoverable_password' => 'encrypted',
        ];
    }
}
