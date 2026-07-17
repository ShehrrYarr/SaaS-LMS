@extends('layouts.superadmin')

@section('title', $tenant->name)
@section('page-title', $tenant->name)
@section('page-subtitle', 'Laboratory details and status management')

@section('topbar-actions')
<a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="btn-secondary text-sm">
    Edit Laboratory
</a>
@endsection

@section('content')
{{-- Credentials banner: shown after creation OR after password reset --}}
@php
    $credsBanner = session('lab_credentials') ?? (session('reset_credentials') ? array_merge(session('reset_credentials'), ['login_url' => url($tenant->slug.'/login')]) : null);
    $isReset = session('reset_credentials') && !session('lab_credentials');
@endphp
@if($credsBanner)
<div class="glass-card p-6 mb-6 border-2" style="border-color: rgba(99,102,241,0.5); background: rgba(99,102,241,0.1);">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgba(99,102,241,0.3);">
            <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-white font-bold text-base mb-1">
                {{ $isReset ? 'Password Reset — Save the New Password' : 'Lab Admin Credentials — Save These Now' }}
            </h3>
            <p class="text-white/50 text-sm mb-4">This password is shown <strong class="text-yellow-400">only once</strong> and cannot be recovered. Copy it before leaving this page.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-xl p-3" style="background: rgba(0,0,0,0.3);">
                    <p class="text-white/40 text-xs mb-1">Login URL</p>
                    <a href="{{ $credsBanner['login_url'] }}" target="_blank" class="text-indigo-300 text-sm break-all hover:underline">{{ $credsBanner['login_url'] }}</a>
                </div>
                <div class="rounded-xl p-3" style="background: rgba(0,0,0,0.3);">
                    <p class="text-white/40 text-xs mb-1">Email</p>
                    <p class="text-white font-mono text-sm select-all">{{ $credsBanner['email'] }}</p>
                </div>
                <div class="rounded-xl p-3" style="background: rgba(0,0,0,0.3);">
                    <p class="text-white/40 text-xs mb-1">Password</p>
                    <p class="text-yellow-300 font-mono text-sm font-bold select-all tracking-wider">{{ $credsBanner['password'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Info card --}}
    <div class="lg:col-span-2 glass-card p-6 space-y-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0"
                 style="background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(139,92,246,0.2));">
                <span class="text-white font-bold text-2xl">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">{{ $tenant->name }}</h2>
                <p class="text-white/40 text-sm">/{{ $tenant->slug }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="badge badge-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'warning' : 'gray') }}">
                        {{ ucfirst($tenant->status) }}
                    </span>
                    <span class="badge badge-purple">{{ $tenant->plan->name ?? 'No Plan' }}</span>
                    @if($tenant->is_demo)
                    <span class="badge badge-info">Demo Lab</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/10">
            @foreach([['Email', $tenant->email], ['Phone', $tenant->phone], ['Address', $tenant->address], ['Created', $tenant->created_at->format('M d, Y')]] as [$label, $value])
            <div>
                <p class="text-white/40 text-xs uppercase tracking-wider">{{ $label }}</p>
                <p class="text-white text-sm mt-1">{{ $value ?? '—' }}</p>
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-3 gap-4 pt-4 border-t border-white/10">
            <div class="glass-card p-4 text-center">
                <p class="text-2xl font-bold text-white">{{ $staffCount }}</p>
                <p class="text-white/40 text-xs mt-1">Staff Members</p>
            </div>
            <div class="glass-card p-4 text-center">
                <p class="text-2xl font-bold text-white">{{ $patientCount }}</p>
                <p class="text-white/40 text-xs mt-1">Patients</p>
            </div>
            <div class="glass-card p-4 text-center">
                <p class="text-2xl font-bold text-white">{{ $tenant->plan->max_staff ?? '∞' }}</p>
                <p class="text-white/40 text-xs mt-1">Staff Limit</p>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="glass-card p-6">
        <h3 class="text-white font-semibold mb-4">Status Management</h3>
        <div class="space-y-2">
            @foreach(['active' => ['success', 'Activate'], 'suspended' => ['warning', 'Suspend'], 'inactive' => ['gray', 'Deactivate']] as $status => [$color, $label])
            @if($tenant->status !== $status)
            <form method="POST" action="{{ route('superadmin.tenants.status', $tenant) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $status }}">
                <button type="submit"
                        class="w-full text-left px-4 py-3 rounded-xl text-sm text-white/70 hover:text-white transition-all"
                        style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                    {{ $label }} Laboratory
                </button>
            </form>
            @else
            <div class="px-4 py-3 rounded-xl text-sm text-white/30 border border-white/5 bg-white/3">
                Currently {{ $label }}d
            </div>
            @endif
            @endforeach
        </div>

        <div class="mt-6 pt-4 border-t border-white/10">
            <p class="text-white/40 text-xs mb-3">Landing Page Demo</p>
            <form method="POST" action="{{ route('superadmin.tenants.toggle-demo', $tenant) }}"
                  @if(!$tenant->is_demo) onsubmit="return confirm('Feature this laboratory as the public demo on the landing page? Visitors will be able to log in with one click. Any other demo lab will be unmarked.')" @endif>
                @csrf
                <button type="submit"
                        class="w-full text-left px-4 py-3 rounded-xl text-sm transition-all"
                        style="{{ $tenant->is_demo
                            ? 'background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.35); color: #a5b4fc;'
                            : 'background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); color: rgba(255,255,255,0.7);' }}">
                    {{ $tenant->is_demo ? 'Unmark as Demo Lab' : 'Mark as Demo Lab' }}
                </button>
            </form>
            @if($tenant->is_demo)
            <p class="text-white/30 text-xs mt-2">Featured on the landing page. Destructive actions are disabled inside this lab.</p>
            @endif
        </div>

        <div class="mt-6 pt-4 border-t border-white/10">
            <p class="text-white/40 text-xs mb-3">Lab Access URL</p>
            <div class="flex items-center gap-2 p-3 rounded-xl bg-white/5 border border-white/10">
                <code class="text-indigo-300 text-xs flex-1 truncate">
                    {{ url($tenant->slug . '/login') }}
                </code>
                <button onclick="navigator.clipboard.writeText('{{ url($tenant->slug . '/login') }}')"
                        class="text-white/40 hover:text-white/70 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Lab Admin account --}}
<div class="glass-card p-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-white font-semibold">Lab Admin Account</h3>
        @if($labAdmin)
        <form method="POST" action="{{ route('superadmin.tenants.reset-password', $tenant) }}"
              onsubmit="return confirm('Reset the Lab Admin password? The new password will be shown once.')">
            @csrf
            <button type="submit" class="btn-secondary text-xs py-2 px-4">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reset Password
            </button>
        </form>
        @endif
    </div>
    @if($labAdmin)
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
            <p class="text-white/40 text-xs uppercase tracking-wider mb-1">Name</p>
            <p class="text-white text-sm font-medium">{{ $labAdmin->name }}</p>
        </div>
        <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
            <p class="text-white/40 text-xs uppercase tracking-wider mb-1">Email (Login)</p>
            <p class="text-white font-mono text-sm select-all">{{ $labAdmin->email }}</p>
        </div>
        <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
            <p class="text-white/40 text-xs uppercase tracking-wider mb-1">Password</p>
            @if($labAdmin->recoverable_password)
            <div x-data="{ show: false }" class="flex items-center justify-between gap-3">
                <p class="text-yellow-300 font-mono text-sm font-bold select-all tracking-wider"
                   x-text="show ? {{ Js::from($labAdmin->recoverable_password) }} : '••••••••••••'"></p>
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
            <p class="text-white/40 text-sm italic">Not captured yet — reset the password to capture it</p>
            @endif
        </div>
    </div>
    @else
    <p class="text-white/30 text-sm">No staff account found. Create a staff member from the lab panel.</p>
    @endif
</div>
@endsection
