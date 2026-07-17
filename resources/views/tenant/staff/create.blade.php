@extends('layouts.tenant')

@section('title', 'Add Staff')
@section('page-title', 'Add Staff Member')

@section('content')
<div class="max-w-2xl">
    <div class="glass-card p-8">
        <form method="POST" action="{{ route('tenant.staff.store', $currentTenant->slug) }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="form-label">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="glass-input" placeholder="Dr. Jane Smith" required>
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="glass-input" required>
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="glass-input" placeholder="+1-555-0100">
                </div>

                <div>
                    <label class="form-label">Password <span class="text-red-400">*</span></label>
                    <input type="password" name="password" class="glass-input" required minlength="8">
                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="glass-input" required>
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Assign Role</label>
                    <select name="role_id" class="glass-input">
                        <option value="">No role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-white/30 text-xs mt-1">Manage roles and their permissions in the Roles section.</p>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                <button type="submit" class="btn-primary">Add Staff Member</button>
                <a href="{{ route('tenant.staff.index', $currentTenant->slug) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
