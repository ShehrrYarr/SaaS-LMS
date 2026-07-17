<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'plan_id', 'name', 'slug', 'logo', 'phone', 'email', 'address', 'status', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'is_demo' => 'boolean',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function settings()
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings()->where('key', $key)->value('value') ?? $default;
    }

    public function setSetting(string $key, mixed $value): void
    {
        $this->settings()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
