<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'invoice_id', 'method', 'bank_id', 'amount', 'notes', 'paid_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bank()
    {
        return $this->belongsTo(LabBank::class, 'bank_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getMethodLabelAttribute(): string
    {
        if ($this->method === 'cash') {
            return 'Cash';
        }
        return $this->bank ? $this->bank->name : 'Bank';
    }
}
