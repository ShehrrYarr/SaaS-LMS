<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TestCatalog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'category', 'price',
        'unit', 'normal_range', 'result_type', 'description', 'is_panel', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_panel'  => 'boolean',
            'is_active' => 'boolean',
            'price'     => 'decimal:2',
        ];
    }

    /**
     * The ordered list of items (headers + tests) that make up this panel.
     */
    public function panelItems()
    {
        return $this->hasMany(PanelItem::class, 'panel_id')->orderBy('sort_order');
    }

    /**
     * Convenience: the actual single tests inside this panel, in order.
     */
    public function panelTests()
    {
        return $this->belongsToMany(
            TestCatalog::class,
            'panel_items',
            'panel_id',
            'test_id'
        )->wherePivot('type', 'test')->withPivot('sort_order', 'header_label')->orderByPivot('sort_order');
    }

    public function orderItems()
    {
        return $this->hasMany(TestOrderItem::class);
    }

    /**
     * Whether this panel contains nested panels (making it ineligible for nesting itself).
     */
    public function containsPanels(): bool
    {
        return $this->panelItems()->where('type', 'panel')->exists();
    }

    /**
     * Whether this panel is nested inside any other panel.
     */
    public function isNestedAnywhere(): bool
    {
        return PanelItem::where('type', 'panel')->where('test_id', $this->id)->exists();
    }

    /**
     * Total number of tests in this panel, including tests inside nested panels.
     * Callers should eager-load panelItems.test.panelItems to keep this query-free.
     */
    public function totalTestCount(): int
    {
        return $this->panelItems->sum(fn ($pi) => match ($pi->type) {
            'test'  => 1,
            'panel' => $pi->test?->panelItems->where('type', 'test')->count() ?? 0,
            default => 0,
        });
    }

    public function isTextResult(): bool
    {
        return $this->result_type === 'text';
    }
}
