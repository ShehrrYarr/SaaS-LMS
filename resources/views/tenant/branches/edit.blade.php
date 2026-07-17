@extends('layouts.tenant')

@section('title', 'Edit Branch')
@section('page-title', 'Edit Branch — ' . $branch->name)

@section('content')
<div class="max-w-2xl">
    <div class="glass-card p-8">
        <form method="POST" action="{{ route('tenant.branches.update', [$currentTenant->slug, $branch]) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="form-label">Branch Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $branch->name) }}" class="glass-input" required>
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $branch->email) }}" class="glass-input" required>
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Phone <span class="text-red-400">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="glass-input" required>
                    <p class="text-white/30 text-xs mt-1">The branch can log in with this number or the email.</p>
                    @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="glass-input" rows="2">{{ old('address', $branch->address) }}</textarea>
                    @error('address') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-white/10 hover:border-white/20 cursor-pointer transition-colors">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $branch->is_active)) class="rounded border-white/20 bg-white/5 text-indigo-500">
                        <span class="text-sm text-white/70">Active — the branch can log in and work</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('tenant.branches.show', [$currentTenant->slug, $branch]) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
