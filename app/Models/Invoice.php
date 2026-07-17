<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class Invoice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'patient_id', 'test_order_id', 'invoice_number',
        'subtotal', 'discount', 'total', 'amount_paid', 'status', 'notes', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'    => 'decimal:2',
            'discount'    => 'decimal:2',
            'total'       => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'paid_at'     => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function testOrder()
    {
        return $this->belongsTo(TestOrder::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class)->latest();
    }

    public function syncPaymentTotals(): void
    {
        $paid = (float) $this->payments()->withoutGlobalScope('tenant')->sum('amount');

        $status = match(true) {
            $paid >= (float) $this->total => 'paid',
            $paid > 0                     => 'partial',
            default                       => 'unpaid',
        };

        $this->update([
            'amount_paid' => $paid,
            'status'      => $status,
            'paid_at'     => $status === 'paid' ? now() : null,
        ]);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_paid);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'paid'      => 'success',
            'partial'   => 'warning',
            'unpaid'    => 'danger',
            'cancelled' => 'gray',
            default     => 'gray',
        };
    }
}
