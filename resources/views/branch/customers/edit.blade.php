@extends('layouts.branch')

@section('title', 'Edit Customer')
@section('page-title', 'Edit Customer — ' . $patient->name)

@section('topbar-actions')
<a href="{{ route('branch.customers.show', [$currentTenant->slug, $patient]) }}" class="btn-secondary text-sm">&larr; Back</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('branch.customers.update', [$currentTenant->slug, $patient]) }}" class="space-y-6">
        @csrf @method('PUT')
        <div class="glass-card p-8 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="form-label">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $patient->name) }}" class="glass-input" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Email Address <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $patient->email) }}" class="glass-input" required>
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}" class="glass-input">
                </div>

                <div>
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $patient->dob?->format('Y-m-d')) }}" class="glass-input">
                </div>

                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="glass-input">
                        <option value="">Prefer not to say</option>
                        <option value="male" @selected(old('gender', $patient->gender) === 'male')>Male</option>
                        <option value="female" @selected(old('gender', $patient->gender) === 'female')>Female</option>
                        <option value="other" @selected(old('gender', $patient->gender) === 'other')>Other</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="glass-input">
                        <option value="">Unknown</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" @selected(old('blood_group', $patient->blood_group) === $bg)>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="glass-input" rows="2">{{ old('address', $patient->address) }}</textarea>
                </div>
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
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('branch.customers.show', [$currentTenant->slug, $patient]) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
