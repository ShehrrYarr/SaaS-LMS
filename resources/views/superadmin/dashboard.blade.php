@extends('layouts.superadmin')

@section('title', 'Dashboard')
@section('page-title', 'Platform Dashboard')
@section('page-subtitle', 'Overview of all laboratories and platform metrics')

@section('topbar-actions')
<a href="{{ route('superadmin.tenants.create') }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Add Laboratory
</a>
@endsection

@section('content')
{{-- Stats grid --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    @php
    $statCards = [
        ['label' => 'Total Labs',     'value' => $stats['total_labs'],    'color' => 'indigo', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['label' => 'Active Labs',    'value' => $stats['active_labs'],   'color' => 'emerald', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Suspended',      'value' => $stats['suspended_labs'],'color' => 'amber',  'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        ['label' => 'Total Staff',    'value' => $stats['total_staff'],   'color' => 'blue',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ['label' => 'Total Patients', 'value' => $stats['total_patients'],'color' => 'purple', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['label' => 'Active Plans',   'value' => $stats['total_plans'],   'color' => 'rose',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ];
    @endphp

    @foreach($statCards as $card)
    <div class="glass-card p-5 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                 style="background: rgba({{ match($card['color']) { 'indigo' => '99,102,241', 'emerald' => '16,185,129', 'amber' => '245,158,11', 'blue' => '59,130,246', 'purple' => '168,85,247', 'rose' => '244,63,94', default => '99,102,241' } }}, 0.2)">
                <svg class="w-5 h-5" style="color: {{ match($card['color']) { 'indigo' => '#818cf8', 'emerald' => '#34d399', 'amber' => '#fbbf24', 'blue' => '#60a5fa', 'purple' => '#c084fc', 'rose' => '#fb7185', default => '#818cf8' } }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                </svg>
            </div>
        </div>
        <div>
            <p class="text-2xl font-bold text-white" x-data x-init="
                let start = 0; const end = {{ $card['value'] }};
                const timer = setInterval(() => { start += Math.ceil(end / 20); if (start >= end) { start = end; clearInterval(timer); } $el.textContent = start; }, 50);
            ">{{ $card['value'] }}</p>
            <p class="text-white/50 text-xs mt-0.5">{{ $card['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Recent Laboratories --}}
    <div class="lg:col-span-2 glass-card p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-white font-semibold">Recent Laboratories</h3>
            <a href="{{ route('superadmin.tenants.index') }}" class="text-indigo-400 text-sm hover:text-indigo-300 transition-colors">View all →</a>
        </div>

        @if($recentTenants->isEmpty())
        <div class="text-center py-10">
            <p class="text-white/30 text-sm">No laboratories yet. <a href="{{ route('superadmin.tenants.create') }}" class="text-indigo-400 hover:underline">Create one</a></p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($recentTenants as $tenant)
            <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-white/5 transition-colors group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(139,92,246,0.2));">
                    <span class="text-white font-semibold text-sm">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ $tenant->name }}</p>
                    <p class="text-white/40 text-xs truncate">{{ $tenant->slug }} · {{ $tenant->plan->name ?? 'No Plan' }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="badge badge-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'warning' : 'gray') }}">
                        {{ ucfirst($tenant->status) }}
                    </span>
                    <a href="{{ route('superadmin.tenants.show', $tenant) }}"
                       class="opacity-0 group-hover:opacity-100 text-white/40 hover:text-white transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Plan Distribution --}}
    <div class="glass-card p-6">
        <h3 class="text-white font-semibold mb-6">Plan Distribution</h3>

        @if($planDistribution->isEmpty())
        <p class="text-white/30 text-sm text-center py-6">No plans configured.</p>
        @else
        <div class="space-y-4">
            @foreach($planDistribution as $plan)
            @php $pct = $stats['total_labs'] > 0 ? round(($plan->tenants_count / $stats['total_labs']) * 100) : 0; @endphp
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-white/70 text-sm">{{ $plan->name }}</span>
                    <span class="text-white font-semibold text-sm">{{ $plan->tenants_count }}</span>
                </div>
                <div class="h-2 rounded-full bg-white/10 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700"
                         style="width: {{ $pct }}%; background: linear-gradient(90deg, #6366f1, #8b5cf6);"></div>
                </div>
                <p class="text-white/30 text-xs mt-0.5">{{ $pct }}% of all labs</p>
            </div>
            @endforeach
        </div>

        <div class="mt-6 pt-4 border-t border-white/10">
            <a href="{{ route('superadmin.plans.index') }}" class="btn-secondary w-full text-sm justify-center">
                Manage Plans
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
