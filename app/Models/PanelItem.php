<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanelItem extends Model
{
    protected $fillable = [
        'panel_id', 'type', 'test_id', 'header_label', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function panel()
    {
        return $this->belongsTo(TestCatalog::class, 'panel_id');
    }

    public function test()
    {
        return $this->belongsTo(TestCatalog::class, 'test_id');
    }

    public function isHeader(): bool
    {
        return $this->type === 'header';
    }

    public function isPanelRef(): bool
    {
        return $this->type === 'panel';
    }
}
