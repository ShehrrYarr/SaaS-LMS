<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Branch extends Authenticatable
{
    use Notifiable, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'email', 'phone', 'password',
        'recoverable_password', 'address', 'is_active',
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

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function testOrders()
    {
        return $this->hasMany(TestOrder::class);
    }
}
