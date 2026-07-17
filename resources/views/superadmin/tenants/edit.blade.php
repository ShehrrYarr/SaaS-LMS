@extends('layouts.superadmin')

@section('title', 'Edit Laboratory')
@section('page-title', 'Edit Laboratory')
@section('page-subtitle', $tenant->name)

@section('content')
<div class="max-w-2xl">
    <div class="glass-card p-8">
        <form method="POST" action="{{ route('superadmin.tenants.update', $tenant) }}" class="space-y-6">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="sm:col-span-2">
                    <label class="form-label">Laboratory Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" class="glass-input" required>
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Plan <span class="text-red-400">*</span></label>
                    <select name="plan_id" class="glass-input" required>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(old('plan_id', $tenant->plan_id) == $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    @error('plan_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">URL Slug</label>
                    <input type="text" value="{{ $tenant->slug }}" class="glass-input opacity-50" disabled>
                    <p class="text-white/25 text-xs mt-1">Slug cannot be changed after creation.</p>
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $tenant->email) }}" class="glass-input">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" class="glass-input">
                    @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="glass-input" rows="2">{{ old('address', $tenant->address) }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('superadmin.tenants.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
