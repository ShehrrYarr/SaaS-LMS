@extends('layouts.auth')

@section('title', 'Superadmin Login')
@section('brand-subtitle', 'Platform Administration')

@section('content')
<div x-data="{ loading: false }">
    <h2 class="text-xl font-bold text-white mb-1">Welcome back</h2>
    <p class="text-white/50 text-sm mb-6">Sign in to the admin dashboard</p>

    @if ($errors->any())
    <div class="mb-4 p-4 rounded-xl border border-red-500/30 bg-red-500/10 text-red-300 text-sm animate-scale-in">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('superadmin.login.post') }}"
          @submit="loading = true" class="space-y-4">
        @csrf

        <div>
            <label class="form-label">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="glass-input" placeholder="admin@example.com" required autofocus>
        </div>

        <div>
            <label class="form-label">Password</label>
            <div class="relative" x-data="{ show: false }">
                <input :type="show ? 'text' : 'password'" name="password"
                       class="glass-input pr-12" placeholder="••••••••" required>
                <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/70 transition-colors">
                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-white/20 bg-white/5 text-indigo-500">
                <span class="text-sm text-white/60">Keep me signed in</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full mt-2" :disabled="loading">
            <span x-show="!loading">Sign In to Dashboard</span>
            <span x-show="loading" class="flex items-center gap-2" style="display:none">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing in...
            </span>
        </button>
    </form>

    <div class="mt-6 pt-6 border-t border-white/10">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <p class="text-xs text-white/40">Superadmin access only. Lab staff should use their lab-specific login URL.</p>
        </div>
    </div>
</div>
@endsection
