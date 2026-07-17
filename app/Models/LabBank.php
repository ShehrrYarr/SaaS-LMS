<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class LabBank extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'account_title', 'account_number', 'branch', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class, 'bank_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
