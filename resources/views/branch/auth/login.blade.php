@extends('layouts.auth')

@section('title', 'Branch Portal')
@section('brand-subtitle', $currentTenant->name . ' — Branch Portal')

@section('content')
<div x-data="{ loading: false }">
    @if($currentTenant->logo)
    <div class="flex justify-center mb-4">
        <img src="{{ Storage::url($currentTenant->logo) }}" alt="{{ $currentTenant->name }}" class="h-12 object-contain">
    </div>
    @endif

    <h2 class="text-xl font-bold text-white mb-1">Branch Login</h2>
    <p class="text-white/50 text-sm mb-6">Sign in to your branch account</p>

    @if ($errors->any())
    <div class="mb-4 p-4 rounded-xl border border-red-500/30 bg-red-500/10 text-red-300 text-sm animate-scale-in">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('branch.login.post', $currentTenant->slug) }}"
          @submit="loading = true" class="space-y-4">
        @csrf

        <div>
            <label class="form-label">Email or Phone Number</label>
            <input type="text" name="identifier" value="{{ old('identifier') }}"
                   class="glass-input" placeholder="branch@lab.com or +1-555-0101" required autofocus>
        </div>

        <div>
            <label class="form-label">Password</label>
            <input type="password" name="password" class="glass-input" placeholder="••••••••" required>
        </div>

        <label class="flex items-center gap-2 text-sm text-white/50">
            <input type="checkbox" name="remember" value="1" class="rounded border-white/20 bg-white/5 text-indigo-500">
            Keep me signed in
        </label>

        <button type="submit" class="btn-primary w-full mt-2" :disabled="loading">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span x-show="!loading">Sign In</span>
            <span x-show="loading" style="display:none">Loading...</span>
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('tenant.login', $currentTenant->slug) }}" class="text-xs text-white/40 hover:text-white/60 transition-colors">
            ← Staff Login
        </a>
    </div>
</div>
@endsection
