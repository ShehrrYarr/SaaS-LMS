@extends('layouts.auth')

@section('title', 'Patient Portal')
@section('brand-subtitle', $currentTenant->name . ' — Patient Portal')

@section('content')
<div x-data="{ loading: false }">
    @if($currentTenant->logo)
    <div class="flex justify-center mb-4">
        <img src="{{ Storage::url($currentTenant->logo) }}" alt="{{ $currentTenant->name }}" class="h-12 object-contain">
    </div>
    @endif

    <h2 class="text-xl font-bold text-white mb-1">Patient Portal</h2>
    <p class="text-white/50 text-sm mb-6">View your test results and reports</p>

    @if ($errors->any())
    <div class="mb-4 p-4 rounded-xl border border-red-500/30 bg-red-500/10 text-red-300 text-sm animate-scale-in">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('patient.login.post', $currentTenant->slug) }}"
          @submit="loading = true" class="space-y-4">
        @csrf

        <div>
            <label class="form-label">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="glass-input" placeholder="patient@email.com" required autofocus>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span x-show="!loading">Access My Portal</span>
            <span x-show="loading" style="display:none">Loading...</span>
        </button>
    </form>

    <div class="mt-6 p-4 rounded-xl bg-blue-500/10 border border-blue-500/20">
        <p class="text-xs text-blue-300/80">
            <strong class="text-blue-300">New patient?</strong> Your login credentials were sent to your email when you registered at {{ $currentTenant->name }}.
        </p>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('tenant.login', $currentTenant->slug) }}" class="text-xs text-white/40 hover:text-white/60 transition-colors">
            ← Staff Login
        </a>
    </div>
</div>
@endsection
