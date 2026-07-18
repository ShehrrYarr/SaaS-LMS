@extends('layouts.superadmin')

@section('title', 'Settings')
@section('page-title', 'Account Settings')
@section('page-subtitle', 'Manage your superadmin profile and password')

@section('content')
<div class="max-w-2xl space-y-6">

    {{-- Profile --}}
    <div class="glass-card p-8">
        <div class="mb-6">
            <h3 class="text-white font-semibold">Profile</h3>
            <p class="text-white/40 text-sm mt-1">Your name and the email you use to sign in.</p>
        </div>

        <form method="POST" action="{{ route('superadmin.settings.profile') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" class="glass-input" required>
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Login Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="glass-input" required>
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                <button type="submit" class="btn-primary">Save Profile</button>
            </div>
        </form>
    </div>

    {{-- Current password --}}
    <div class="glass-card p-8">
        <div class="mb-5">
            <h3 class="text-white font-semibold">Current Password</h3>
        </div>

        <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
            <p class="text-white/40 text-xs uppercase tracking-wider mb-1">Password</p>
            @if($admin->recoverable_password)
            <div x-data="{ show: false }" class="flex items-center justify-between gap-3">
                <p class="text-yellow-300 font-mono text-sm font-bold select-all tracking-wider"
                   x-text="show ? {{ Js::from($admin->recoverable_password) }} : '••••••••••••'"></p>
                <button type="button" @click="show = !show"
                        class="text-white/40 hover:text-white/70 transition-colors flex-shrink-0">
                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            @else
            <p class="text-white/40 text-sm italic">Not captured yet — change your password below to capture it</p>
            @endif
        </div>
    </div>

    {{-- Change password --}}
    <div class="glass-card p-8">
        <div class="mb-6">
            <h3 class="text-white font-semibold">Change Password</h3>
            <p class="text-white/40 text-sm mt-1">You'll use the new password the next time you sign in. It will also appear in the Current Password card above.</p>
        </div>

        <form method="POST" action="{{ route('superadmin.settings.password') }}" class="space-y-5 max-w-md">
            @csrf

            <div>
                <label class="form-label">New Password <span class="text-red-400">*</span></label>
                <input type="password" name="password" class="glass-input" placeholder="Minimum 8 characters" required minlength="8" autocomplete="new-password">
                @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Confirm New Password <span class="text-red-400">*</span></label>
                <input type="password" name="password_confirmation" class="glass-input" placeholder="Repeat the new password" required autocomplete="new-password">
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                <button type="submit" class="btn-primary">Change Password</button>
            </div>
        </form>
    </div>

    {{-- Deployment tools --}}
    <div class="glass-card p-8">
        <div class="mb-6">
            <h3 class="text-white font-semibold">Deployment Tools</h3>
            <p class="text-white/40 text-sm mt-1">Run server maintenance commands without SSH. Typical update flow: <span class="font-mono text-xs">Git Pull → Migrate → Optimize</span>.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <form method="POST" action="{{ route('superadmin.settings.deploy.git-pull') }}"
                  onsubmit="return confirm('Pull the latest code from GitHub?')">
                @csrf
                <button type="submit" class="btn-secondary w-full">
                    <span class="block font-semibold">Git Pull</span>
                    <span class="block text-xs opacity-60 mt-0.5">Fetch latest code</span>
                </button>
            </form>

            <form method="POST" action="{{ route('superadmin.settings.deploy.migrate') }}"
                  onsubmit="return confirm('Run database migrations? New tables/columns will be created.')">
                @csrf
                <button type="submit" class="btn-secondary w-full">
                    <span class="block font-semibold">Migrate</span>
                    <span class="block text-xs opacity-60 mt-0.5">Update database schema</span>
                </button>
            </form>

            <form method="POST" action="{{ route('superadmin.settings.deploy.optimize') }}">
                @csrf
                <button type="submit" class="btn-secondary w-full">
                    <span class="block font-semibold">Optimize</span>
                    <span class="block text-xs opacity-60 mt-0.5">Clear &amp; rebuild caches</span>
                </button>
            </form>
        </div>

        @if(session('deploy_output'))
        <div class="mt-6">
            <p class="text-white/40 text-xs uppercase tracking-wider mb-2">Command Output</p>
            <pre class="rounded-xl p-4 text-xs font-mono whitespace-pre-wrap overflow-x-auto"
                 style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.08); color: #6ee7b7; max-height: 320px; overflow-y: auto;">{{ session('deploy_output') }}</pre>
        </div>
        @endif
    </div>
</div>
@endsection
