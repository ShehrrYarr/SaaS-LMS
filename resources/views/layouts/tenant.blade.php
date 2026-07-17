@extends('layouts.app')

@section('sidebar-title', $currentTenant->name ?? 'Laboratory')
@section('sidebar-subtitle', 'Lab Management System')

@section('sidebar-nav')
@php $slug = $currentTenant->slug; @endphp

<a href="{{ route('tenant.dashboard', $slug) }}"
   class="nav-link {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
    </svg>
    Dashboard
</a>

<div class="pt-3 pb-1 px-1">
    <p class="text-xs font-semibold uppercase tracking-widest" style="color: #cbd5e1;">Patients</p>
</div>

<a href="{{ route('tenant.patients.index', $slug) }}"
   class="nav-link {{ request()->routeIs('tenant.patients.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    Patients
</a>

<a href="{{ route('tenant.appointments.index', $slug) }}"
   class="nav-link {{ request()->routeIs('tenant.appointments.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    Appointments
</a>

<div class="pt-3 pb-1 px-1">
    <p class="text-xs font-semibold uppercase tracking-widest" style="color: #cbd5e1;">Laboratory</p>
</div>

<a href="{{ route('tenant.tests.index', $slug) }}"
   class="nav-link {{ request()->routeIs('tenant.tests.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
    </svg>
    Test Catalog
</a>

<a href="{{ route('tenant.orders.index', $slug) }}"
   class="nav-link {{ request()->routeIs('tenant.orders.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
    </svg>
    Test Orders
</a>

<div class="pt-3 pb-1 px-1">
    <p class="text-xs font-semibold uppercase tracking-widest" style="color: #cbd5e1;">Finance</p>
</div>

<a href="{{ route('tenant.billing.index', $slug) }}"
   class="nav-link {{ request()->routeIs('tenant.billing.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
    </svg>
    Billing
</a>

@can('manage-staff')
<div class="pt-3 pb-1 px-1">
    <p class="text-xs font-semibold uppercase tracking-widest" style="color: #cbd5e1;">Admin</p>
</div>

<a href="{{ route('tenant.staff.index', $slug) }}"
   class="nav-link {{ request()->routeIs('tenant.staff.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    Staff
</a>

<a href="{{ route('tenant.roles.index', $slug) }}"
   class="nav-link {{ request()->routeIs('tenant.roles.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
    </svg>
    Roles & Permissions
</a>

@if(($currentTenant->plan?->max_branches ?? 0) > 0)
<a href="{{ route('tenant.branches.index', $slug) }}"
   class="nav-link {{ request()->routeIs('tenant.branches.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
    </svg>
    Branches
</a>
@endif

<a href="{{ route('tenant.settings.index', $slug) }}"
   class="nav-link {{ request()->routeIs('tenant.settings.*') ? 'active' : '' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    Settings
</a>
@endcan
@endsection

@section('sidebar-user')
<div class="flex items-center gap-3 p-3 rounded-xl" style="background: rgba(0,0,0,0.04);">
    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
         style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
        <span style="color:#fff; font-size:0.75rem; font-weight:700;">{{ substr(auth()->user()->name, 0, 1) }}</span>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-xs font-medium truncate" style="color: #1e293b;">{{ auth()->user()->name }}</p>
        <p class="text-xs truncate" style="color: #94a3b8;">{{ auth()->user()->getRoleNames()->first() ?? 'Staff' }}</p>
    </div>
    <form method="POST" action="{{ route('tenant.logout', $currentTenant->slug) }}">
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
