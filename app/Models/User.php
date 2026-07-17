<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'email', 'password', 'recoverable_password', 'phone', 'avatar', 'is_active',
    ];

    protected $hidden = ['password', 'recoverable_password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'             => 'hashed',
            'recoverable_password' => 'encrypted',
            'is_active'            => 'boolean',
        ];
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    public function testOrders()
    {
        return $this->hasMany(TestOrder::class, 'created_by');
    }
}
