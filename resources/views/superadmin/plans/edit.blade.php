@extends('layouts.superadmin')

@section('title', 'Edit Plan')
@section('page-title', 'Edit Plan — ' . $plan->name)

@section('content')
<div class="max-w-xl">
    <div class="glass-card p-8">
        <form method="POST" action="{{ route('superadmin.plans.update', $plan) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Plan Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $plan->name) }}" class="glass-input" required>
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Max Staff</label>
                    <input type="number" name="max_staff" value="{{ old('max_staff', $plan->max_staff) }}" class="glass-input" min="1" required>
                </div>
                <div>
                    <label class="form-label">Max Patients</label>
                    <input type="number" name="max_patients" value="{{ old('max_patients', $plan->max_patients) }}" class="glass-input" min="1" required>
                </div>
            </div>

            <div>
                <label class="form-label">Max Branches</label>
                <input type="number" name="max_branches" value="{{ old('max_branches', $plan->max_branches) }}" class="glass-input" min="0" required>
                <p class="text-white/30 text-xs mt-1">0 disables branch management for labs on this plan.</p>
                @error('max_branches') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-3 pt-2">
                <p class="form-label mb-2">Features</p>
                @foreach(['pdf_branding' => 'PDF Branding', 'custom_smtp' => 'Custom SMTP', 'analytics' => 'Advanced Analytics'] as $key => $label)
                <label class="flex items-center gap-3 p-3 rounded-xl border border-white/10 hover:border-white/20 cursor-pointer transition-colors">
                    <input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $plan->$key)) class="rounded border-white/20 bg-white/5 text-indigo-500">
                    <span class="text-sm text-white/70">{{ $label }}</span>
                </label>
                @endforeach
            </div>

            <div>
                <label class="form-label">Status</label>
                <select name="status" class="glass-input">
                    <option value="active" @selected(old('status', $plan->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $plan->status) === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('superadmin.plans.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
