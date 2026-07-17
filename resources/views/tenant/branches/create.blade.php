@extends('layouts.tenant')

@section('title', 'Add Branch')
@section('page-title', 'Add Branch')

@section('content')
<div class="max-w-2xl">
    <div class="glass-card p-8">
        <form method="POST" action="{{ route('tenant.branches.store', $currentTenant->slug) }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="form-label">Branch Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="glass-input" placeholder="Downtown Branch" required>
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="glass-input" placeholder="downtown@yourlab.com" required>
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Phone <span class="text-red-400">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="glass-input" placeholder="+1-555-0101" required>
                    <p class="text-white/30 text-xs mt-1">The branch can log in with this number or the email.</p>
                    @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="glass-input" rows="2" placeholder="Branch street address">{{ old('address') }}</textarea>
                    @error('address') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <p class="text-white/40 text-sm">A login password will be generated automatically and shown after the branch is created.</p>

            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                <button type="submit" class="btn-primary">Create Branch</button>
                <a href="{{ route('tenant.branches.index', $currentTenant->slug) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
