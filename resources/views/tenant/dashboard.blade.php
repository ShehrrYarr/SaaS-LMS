@extends('layouts.tenant')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of ' . $currentTenant->name)

@section('content')
{{-- Stats Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" x-data="{ counts: { patients: 0, appointments: 0, orders: 0, revenue: 0 } }" x-init="
    $nextTick(() => {
        const targets = { patients: {{ $stats['patients'] }}, appointments: {{ $stats['appointments'] }}, orders: {{ $stats['orders'] }}, revenue: {{ $stats['revenue'] }} };
        Object.keys(targets).forEach(k => {
            let start = 0, end = targets[k], dur = 800, step = end / (dur / 16);
            let t = setInterval(() => { start = Math.min(start + step, end); counts[k] = Math.round(start); if (start >= end) clearInterval(t); }, 16);
        });
    })
">
    @php
    $statCards = [
        ['key' => 'patients',     'label' => 'Total Patients',   'color' => 'indigo',  'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'prefix' => '', 'suffix' => ''],
        ['key' => 'appointments', 'label' => "Today's Appointments", 'color' => 'blue', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'prefix' => '', 'suffix' => ''],
        ['key' => 'orders',       'label' => 'Active Orders',    'color' => 'purple', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'prefix' => '', 'suffix' => ''],
        ['key' => 'revenue',      'label' => 'Revenue This Month', 'color' => 'green', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'prefix' => '$', 'suffix' => ''],
    ];
    @endphp
    @foreach($statCards as $card)
    <div class="glass-card p-5 scale-in">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                 style="background: {{ ['indigo'=>'rgba(99,102,241,0.2)','blue'=>'rgba(59,130,246,0.2)','purple'=>'rgba(139,92,246,0.2)','green'=>'rgba(34,197,94,0.2)'][$card['color']] }};">
                <svg class="w-4 h-4 {{ ['indigo'=>'text-indigo-400','blue'=>'text-blue-400','purple'=>'text-purple-400','green'=>'text-green-400'][$card['color']] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-white">
            {{ $card['prefix'] }}<span x-text="counts['{{ $card['key'] }}']{{ $card['key'] === 'revenue' ? '.toFixed(0)' : '' }}"></span>{{ $card['suffix'] }}
        </p>
        <p class="text-white/40 text-sm mt-1">{{ $card['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    <div class="glass-card p-6">
        <h3 class="text-white font-medium mb-4">Patient Registrations (6 months)</h3>
        <div id="patientChart"></div>
    </div>
    <div class="glass-card p-6">
        <h3 class="text-white font-medium mb-4">Revenue (6 months)</h3>
        <div id="revenueChart"></div>
    </div>
</div>

{{-- Bottom row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    {{-- Recent orders --}}
    <div class="glass-card p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-white font-medium">Recent Orders</h3>
            <a href="{{ route('tenant.orders.index', $currentTenant->slug) }}" class="text-indigo-400 text-sm hover:underline">View all</a>
        </div>
        @if($recentOrders->isEmpty())
        <p class="text-white/30 text-sm">No orders yet.</p>
        @else
        <div class="space-y-2">
            @foreach($recentOrders as $order)
            <div class="flex items-center justify-between p-3 rounded-xl bg-white/5">
                <div>
                    <a href="{{ route('tenant.orders.show', [$currentTenant->slug, $order]) }}" class="text-white text-sm hover:text-indigo-300 transition-colors">
                        {{ $order->patient->name ?? '—' }}
                    </a>
                    <p class="text-white/30 text-xs">{{ $order->created_at->diffForHumans() }}</p>
                </div>
                <span class="badge badge-{{ $order->status_color }} text-xs">{{ $order->status_label }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Pending payments --}}
    <div class="glass-card p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-white font-medium">Pending Payments</h3>
            <a href="{{ route('tenant.billing.index', [$currentTenant->slug, 'status' => 'unpaid']) }}" class="text-indigo-400 text-sm hover:underline">View all</a>
        </div>
        @if($pendingPayments->isEmpty())
        <p class="text-white/30 text-sm">All payments collected!</p>
        @else
        <div class="space-y-2">
            @foreach($pendingPayments as $inv)
            <div class="flex items-center justify-between p-3 rounded-xl bg-white/5">
                <div>
                    <a href="{{ route('tenant.billing.show', [$currentTenant->slug, $inv]) }}" class="text-white text-sm hover:text-indigo-300 transition-colors">
                        {{ $inv->patient->name ?? '—' }}
                    </a>
                    <p class="text-white/30 text-xs">{{ $inv->invoice_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-white text-sm font-medium">{{ number_format($inv->total, 2) }}</p>
                    <span class="badge badge-{{ $inv->status_color }} text-xs">{{ ucfirst($inv->status) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const axisLabel  = { colors: 'rgba(30,41,59,0.55)', fontSize: '12px', fontFamily: 'Inter, sans-serif', fontWeight: 500 };

    const commonOpts = {
        chart: {
            type: 'area', height: 230, toolbar: { show: false }, background: 'transparent',
            fontFamily: 'Inter, sans-serif',
            animations: { enabled: true, easing: 'easeinout', speed: 500 },
        },
        stroke: { curve: 'smooth', width: 2.5 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.03, stops: [0, 90, 100] } },
        dataLabels: { enabled: false },
        grid: {
            borderColor: 'rgba(30,41,59,0.08)', strokeDashArray: 4,
            padding: { left: 8, right: 8 },
            yaxis: { lines: { show: true } },
        },
        markers: {
            size: 4, strokeWidth: 2, strokeColors: '#fff', hover: { size: 6 },
        },
        xaxis: {
            labels: { style: axisLabel, offsetY: 2 },
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        yaxis: {
            labels: { style: axisLabel, offsetX: -4 },
        },
        tooltip: {
            theme: 'light',
            style: { fontFamily: 'Inter, sans-serif', fontSize: '12px' },
            x: { show: true },
        },
    };

    @php
    $pgMonths = $patientGrowth->pluck('month')->toJson();
    $pgCounts = $patientGrowth->pluck('count')->toJson();
    $rvMonths = $revenueData->pluck('month')->toJson();
    $rvTotals = $revenueData->pluck('total')->toJson();
    @endphp

    new ApexCharts(document.querySelector('#patientChart'), {
        ...commonOpts,
        series: [{ name: 'Patients', data: {!! $pgCounts !!} }],
        xaxis: { ...commonOpts.xaxis, categories: {!! $pgMonths !!} },
        yaxis: { ...commonOpts.yaxis, labels: { ...commonOpts.yaxis.labels, formatter: (v) => Math.round(v) } },
        colors: ['#6366f1'],
        tooltip: { ...commonOpts.tooltip, y: { formatter: (v) => v + (v === 1 ? ' patient' : ' patients') } },
    }).render();

    new ApexCharts(document.querySelector('#revenueChart'), {
        ...commonOpts,
        series: [{ name: 'Revenue', data: {!! $rvTotals !!} }],
        xaxis: { ...commonOpts.xaxis, categories: {!! $rvMonths !!} },
        yaxis: { ...commonOpts.yaxis, labels: { ...commonOpts.yaxis.labels, formatter: (v) => '$' + (v >= 1000 ? (v / 1000).toFixed(1) + 'k' : Math.round(v)) } },
        colors: ['#22c55e'],
        tooltip: { ...commonOpts.tooltip, y: { formatter: (v) => '$' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) } },
    }).render();
});
</script>
@endpush
