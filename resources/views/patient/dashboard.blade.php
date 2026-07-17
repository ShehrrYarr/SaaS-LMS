@extends('layouts.patient')

@section('title', 'My Dashboard')
@section('page-title', 'Welcome, ' . auth('patient')->user()->name)
@section('page-subtitle', 'Patient code: ' . auth('patient')->user()->patient_code)

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @php
    $statCards = [
        ['label' => 'Test Orders', 'value' => $stats['total_orders'], 'color' => 'indigo', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['label' => 'Reports Ready', 'value' => $stats['ready_reports'], 'color' => 'purple', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['label' => 'Pending Bills', 'value' => $stats['unpaid_invoices'], 'color' => 'warning', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
    ];
    @endphp
    @foreach($statCards as $card)
    <div class="glass-card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0"
             style="background: {{ ['indigo'=>'rgba(99,102,241,0.2)','purple'=>'rgba(139,92,246,0.2)','warning'=>'rgba(234,179,8,0.2)'][$card['color']] }};">
            <svg class="w-5 h-5 {{ ['indigo'=>'text-indigo-400','purple'=>'text-purple-400','warning'=>'text-yellow-400'][$card['color']] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
            </svg>
        </div>
        <div>
            <p class="text-3xl font-bold text-white">{{ $card['value'] }}</p>
            <p class="text-white/40 text-sm">{{ $card['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    {{-- Recent Orders / Reports --}}
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-white font-semibold">Recent Test Orders</h3>
            <a href="{{ route('patient.reports.index', $currentTenant->slug) }}" class="text-indigo-400 text-sm hover:underline">View All</a>
        </div>

        @if($patient->testOrders->isEmpty())
        <div class="text-center py-8">
            <p class="text-white/30 text-sm">No test orders yet.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($patient->testOrders as $order)
            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/8 transition-colors">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                     style="background: {{ match($order->status) { 'finalized','results_ready' => 'rgba(34,197,94,0.2)', 'processing' => 'rgba(59,130,246,0.2)', default => 'rgba(255,255,255,0.08)' } }};">
                    <svg class="w-4 h-4 {{ match($order->status) { 'finalized','results_ready' => 'text-green-400', 'processing' => 'text-blue-400', default => 'text-white/30' } }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium">Order #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-white/30 text-xs">{{ $order->items->count() }} test(s) · {{ $order->created_at->format('d M Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-{{ $order->status_color }} text-xs">{{ $order->status_label }}</span>
                    @if(in_array($order->status, ['results_ready', 'finalized']))
                    <a href="{{ route('patient.reports.download', [$currentTenant->slug, $order]) }}"
                       class="text-indigo-400 hover:text-indigo-300 text-xs hover:underline">PDF</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Recent Invoices --}}
    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-white font-semibold">Recent Invoices</h3>
            <a href="{{ route('patient.invoices.index', $currentTenant->slug) }}" class="text-indigo-400 text-sm hover:underline">View All</a>
        </div>

        @if($patient->invoices->isEmpty())
        <div class="text-center py-8">
            <p class="text-white/30 text-sm">No invoices yet.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($patient->invoices as $inv)
            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/8 transition-colors">
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium">{{ $inv->invoice_number }}</p>
                    <p class="text-white/30 text-xs">{{ $inv->created_at->format('d M Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-white text-sm font-semibold">{{ number_format($inv->total, 2) }}</p>
                    <span class="badge badge-{{ $inv->status_color }} text-xs">{{ ucfirst($inv->status) }}</span>
                </div>
                <a href="{{ route('patient.invoices.download', [$currentTenant->slug, $inv]) }}"
                   class="text-white/30 hover:text-white transition-colors" title="Download">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"/>
                    </svg>
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
