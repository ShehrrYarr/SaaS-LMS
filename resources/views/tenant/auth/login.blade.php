@extends('layouts.auth')

@section('title', 'Staff Login')
@section('brand-subtitle', $currentTenant->name)

@section('content')
<div x-data="{ loading: false }">
    {{-- Lab branding --}}
    @if($currentTenant->logo)
    <div class="flex justify-center mb-4">
        <img src="{{ Storage::url($currentTenant->logo) }}" alt="{{ $currentTenant->name }}" class="h-12 object-contain">
    </div>
    @endif

    <h2 class="text-xl font-bold text-white mb-1">Staff Login</h2>
    <p class="text-white/50 text-sm mb-6">Sign in to {{ $currentTenant->name }}</p>

    @if ($errors->any())
    <div class="mb-4 p-4 rounded-xl border border-red-500/30 bg-red-500/10 text-red-300 text-sm animate-scale-in">
        {{ $errors->first() }}
    </div>
    @endif

    @if (session('success'))
    <div class="mb-4 p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-300 text-sm animate-scale-in">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('tenant.login.post', $currentTenant->slug) }}"
          @submit="loading = true" class="space-y-4">
        @csrf

        <div>
            <label class="form-label">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="glass-input" placeholder="you@lab.com" required autofocus>
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

        <label class="flex items-center gap-2 text-sm text-white/50">
            <input type="checkbox" name="remember" value="1" class="rounded border-white/20 bg-white/5 text-indigo-500">
            Keep me signed in
        </label>

        <button type="submit" class="btn-primary w-full mt-2" :disabled="loading">
            <span x-show="!loading">Sign In</span>
            <span x-show="loading" class="flex items-center gap-2" style="display:none">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing in...
            </span>
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('patient.login', $currentTenant->slug) }}" class="text-xs text-white/40 hover:text-white/60 transition-colors">
            Are you a patient? Click here to access your portal →
        </a>
    </div>
</div>
@endsection
