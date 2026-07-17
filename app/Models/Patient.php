<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Patient extends Authenticatable
{
    use Notifiable, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'branch_id', 'patient_code', 'name', 'email', 'password',
        'phone', 'dob', 'gender', 'address', 'blood_group', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
            'dob'       => 'date',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function testOrders()
    {
        return $this->hasMany(TestOrder::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->dob ? $this->dob->age : null;
    }

    public static function generateCode(int $tenantId): string
    {
        $prefix = 'P' . str_pad($tenantId, 3, '0', STR_PAD_LEFT);
        $last   = static::withoutGlobalScope('tenant')
                        ->where('tenant_id', $tenantId)
                        ->where('patient_code', 'like', $prefix . '%')
                        ->orderByDesc('id')
                        ->value('patient_code');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
