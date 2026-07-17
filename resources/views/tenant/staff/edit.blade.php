@extends('layouts.tenant')

@section('title', 'Edit Staff')
@section('page-title', 'Edit Staff Member')
@section('page-subtitle', $staff->name)

@section('content')
<div class="max-w-2xl">
    <div class="glass-card p-8">
        <form method="POST" action="{{ route('tenant.staff.update', [$currentTenant->slug, $staff]) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}" class="glass-input" required>
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}" class="glass-input" required>
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}" class="glass-input">
                </div>

                <div>
                    <label class="form-label">New Password <span class="text-white/30">(leave blank to keep)</span></label>
                    <input type="password" name="password" class="glass-input" minlength="8">
                </div>

                <div>
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="glass-input">
                </div>

                <div>
                    <label class="form-label">Role</label>
                    <select name="role_id" class="glass-input">
                        <option value="">No role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected($staff->roles->contains('id', $role->id))>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked($staff->is_active)
                               class="rounded border-white/20 bg-white/5 text-indigo-500">
                        <span class="text-white/70 text-sm">Account Active</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('tenant.staff.index', $currentTenant->slug) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
