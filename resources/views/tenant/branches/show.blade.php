@extends('layouts.tenant')

@section('title', $branch->name)
@section('page-title', $branch->name)
@section('page-subtitle', 'Branch details and login credentials')

@section('topbar-actions')
<a href="{{ route('tenant.branches.edit', [$currentTenant->slug, $branch]) }}" class="btn-secondary text-sm">Edit Branch</a>
@endsection

@section('content')
{{-- One-time credentials banner after creation --}}
@if(session('branch_credentials'))
@php $creds = session('branch_credentials'); @endphp
<div class="glass-card p-6 mb-6 border-2" style="border-color: rgba(16,185,129,0.5); background: rgba(16,185,129,0.08);">
    <h3 class="text-white font-bold text-base mb-1">Branch Credentials</h3>
    <p class="text-white/50 text-sm mb-4">Share these with the branch. The password stays visible below on this page.</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-xl p-3" style="background: rgba(0,0,0,0.3);">
            <p class="text-white/40 text-xs mb-1">Login URL</p>
            <a href="{{ $creds['login_url'] }}" target="_blank" class="text-indigo-300 text-sm break-all hover:underline">{{ $creds['login_url'] }}</a>
        </div>
        <div class="rounded-xl p-3" style="background: rgba(0,0,0,0.3);">
            <p class="text-white/40 text-xs mb-1">Email / Phone</p>
            <p class="text-white font-mono text-sm select-all">{{ $creds['email'] }}</p>
            <p class="text-white font-mono text-sm select-all">{{ $creds['phone'] }}</p>
        </div>
        <div class="rounded-xl p-3" style="background: rgba(0,0,0,0.3);">
            <p class="text-white/40 text-xs mb-1">Password</p>
            <p class="text-yellow-300 font-mono text-sm font-bold select-all tracking-wider">{{ $creds['password'] }}</p>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 glass-card p-6 space-y-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0"
                 style="background: linear-gradient(135deg, rgba(16,185,129,0.35), rgba(99,102,241,0.25));">
                <span class="text-white font-bold text-xl">{{ strtoupper(substr($branch->name, 0, 1)) }}</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">{{ $branch->name }}</h2>
                <div class="flex items-center gap-2 mt-2">
                    <span class="badge badge-{{ $branch->is_active ? 'success' : 'gray' }}">
                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/10">
            @foreach([['Email', $branch->email], ['Phone', $branch->phone], ['Address', $branch->address], ['Created', $branch->created_at->format('M d, Y')]] as [$label, $value])
            <div>
                <p class="text-white/40 text-xs uppercase tracking-wider">{{ $label }}</p>
                <p class="text-white text-sm mt-1">{{ $value ?? '—' }}</p>
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/10">
            <div class="glass-card p-4 text-center">
                <p class="text-2xl font-bold text-white">{{ $branch->patients_count }}</p>
                <p class="text-white/40 text-xs mt-1">Customers</p>
            </div>
            <div class="glass-card p-4 text-center">
                <p class="text-2xl font-bold text-white">{{ $branch->test_orders_count }}</p>
                <p class="text-white/40 text-xs mt-1">Test Orders</p>
            </div>
        </div>
    </div>

    {{-- Login credentials --}}
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-white font-semibold">Login Access</h3>
            <form method="POST" action="{{ route('tenant.branches.reset-password', [$currentTenant->slug, $branch]) }}"
                  onsubmit="return confirm('Reset this branch\'s password? The new password will be shown here.')">
                @csrf
                <button type="submit" class="btn-secondary text-xs py-2 px-4">Reset Password</button>
            </form>
        </div>

        <div class="space-y-3">
            <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                <p class="text-white/40 text-xs uppercase tracking-wider mb-1">Login URL</p>
                <code class="text-indigo-300 text-xs break-all">{{ route('branch.login', $currentTenant->slug) }}</code>
            </div>
            <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                <p class="text-white/40 text-xs uppercase tracking-wider mb-1">Email / Phone (Login)</p>
                <p class="text-white font-mono text-sm select-all">{{ $branch->email }}</p>
                <p class="text-white font-mono text-sm select-all">{{ $branch->phone }}</p>
            </div>
            <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                <p class="text-white/40 text-xs uppercase tracking-wider mb-1">Password</p>
                @if($branch->recoverable_password)
                <div x-data="{ show: false }" class="flex items-center justify-between gap-3">
                    <p class="text-yellow-300 font-mono text-sm font-bold select-all tracking-wider"
                       x-text="show ? {{ Js::from($branch->recoverable_password) }} : '••••••••••'"></p>
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
    </div>
</div>
@endsection
