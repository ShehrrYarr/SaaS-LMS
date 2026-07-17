@extends('layouts.branch')

@section('title', 'Register Customer')
@section('page-title', 'Register Customer')
@section('page-subtitle', 'Add a new customer for this branch')

@section('topbar-actions')
<a href="{{ route('branch.customers.index', $currentTenant->slug) }}" class="btn-secondary text-sm">&larr; Back</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('branch.customers.store', $currentTenant->slug) }}" class="space-y-6">
        @csrf
        <div class="glass-card p-8 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="form-label">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="glass-input" required autofocus>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Email Address <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="glass-input" required>
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="glass-input" placeholder="+1 555 000 0000">
                </div>

                <div>
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob') }}" class="glass-input">
                </div>

                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="glass-input">
                        <option value="">Prefer not to say</option>
                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                        <option value="other" @selected(old('gender') === 'other')>Other</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="glass-input">
                        <option value="">Unknown</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" @selected(old('blood_group') === $bg)>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="glass-input" rows="2">{{ old('address') }}</textarea>
                </div>
            </div>

            <div class="border-t border-black/5 pt-5">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="send_credentials" value="1" checked
                           class="mt-0.5 rounded border-white/20 bg-white/10 text-indigo-500 focus:ring-indigo-500/50">
                    <div>
                        <p class="text-sm font-medium" style="color:#1e293b;">Send Portal Credentials</p>
                        <p class="text-xs mt-0.5" style="color:#94a3b8;">The customer will receive a welcome email with their patient-portal login.</p>
                    </div>
                </label>
            </div>
        </div>

        @if($errors->any())
        <div class="glass-card p-4 border-red-500/30 bg-red-500/10">
            <ul class="list-disc list-inside text-red-400 text-sm space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Register Customer</button>
            <a href="{{ route('branch.customers.index', $currentTenant->slug) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
