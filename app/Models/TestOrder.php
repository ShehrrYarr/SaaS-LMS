<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;

class TestOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'patient_id', 'appointment_id', 'created_by', 'branch_id',
        'order_number', 'status', 'sample_collected_at',
        'results_ready_at', 'finalized_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sample_collected_at' => 'datetime',
            'results_ready_at'    => 'datetime',
            'finalized_at'        => 'datetime',
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

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(TestOrderItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function getTotalAttribute(): float
    {
        // Invoice is the source of truth (panels are billed as a bundle, so item
        // prices alone would under-count); fall back to summing item prices.
        if ($this->invoice) {
            return (float) $this->invoice->total;
        }
        return (float) $this->items->sum('price');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'gray',
            'sample_collected' => 'info',
            'processing'       => 'warning',
            'results_ready'    => 'purple',
            'finalized'        => 'success',
            'cancelled'        => 'danger',
            default            => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'          => 'Pending',
            'sample_collected' => 'Sample Collected',
            'processing'       => 'Processing',
            'results_ready'    => 'Results Ready',
            'finalized'        => 'Finalized',
            'cancelled'        => 'Cancelled',
            default            => 'Unknown',
        };
    }
}
