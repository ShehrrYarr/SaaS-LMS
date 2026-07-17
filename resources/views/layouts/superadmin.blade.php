@extends('layouts.app')

@section('sidebar-title', 'Lab Manager')
@section('sidebar-subtitle', 'Super Administration')

@section('sidebar-nav')
<a href="{{ route('superadmin.dashboard') }}"
   class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
    </svg>
    Dashboard
</a>

<div class="pt-3 pb-1 px-1">
    <p class="text-xs font-semibold uppercase tracking-widest" style="color: #cbd5e1;">Management</p>
</div>

<a href="{{ route('superadmin.tenants.index') }}"
   class="nav-link {{ request()->routeIs('superadmin.tenants.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
    </svg>
    Laboratories
</a>

<a href="{{ route('superadmin.plans.index') }}"
   class="nav-link {{ request()->routeIs('superadmin.plans.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
    </svg>
    Plans & Pricing
</a>

<a href="{{ route('superadmin.settings') }}"
   class="nav-link {{ request()->routeIs('superadmin.settings*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    Settings
</a>
@endsection

@section('sidebar-user')
<div class="flex items-center gap-3 p-3 rounded-xl" style="background: rgba(0,0,0,0.04);">
    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
         style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
        <span style="color:#fff; font-size:0.75rem; font-weight:700;">{{ substr(auth('superadmin')->user()->name, 0, 1) }}</span>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-xs font-medium truncate" style="color: #1e293b;">{{ auth('superadmin')->user()->name }}</p>
        <p class="text-xs truncate" style="color: #94a3b8;">Superadmin</p>
    </div>
    <form method="POST" action="{{ route('superadmin.logout') }}">
        @csrf
        <button type="submit" class="transition-colors" style="color: #94a3b8;" title="Logout"
                onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </button>
    </form>
</div>
@endsection
