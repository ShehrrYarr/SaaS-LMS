<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestOrderItem extends Model
{
    protected $fillable = [
        'test_order_id', 'test_catalog_id', 'panel_id', 'panel_name',
        'section_header', 'sort_order', 'price',
        'result_value', 'result_type', 'result_file', 'remarks', 'status',
        'entered_by', 'entered_at',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'sort_order' => 'integer',
            'entered_at' => 'datetime',
        ];
    }

    public function isTextResult(): bool
    {
        return $this->result_type === 'text';
    }

    public function testOrder()
    {
        return $this->belongsTo(TestOrder::class);
    }

    public function testCatalog()
    {
        return $this->belongsTo(TestCatalog::class);
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
