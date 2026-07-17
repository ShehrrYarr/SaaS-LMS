<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'max_staff', 'max_patients', 'max_branches',
        'pdf_branding', 'custom_smtp', 'analytics', 'status',
    ];

    protected function casts(): array
    {
        return [
            'pdf_branding' => 'boolean',
            'custom_smtp'  => 'boolean',
            'analytics'    => 'boolean',
        ];
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
