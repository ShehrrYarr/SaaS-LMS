@extends('layouts.branch')

@section('title', 'Order #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))
@section('page-title', 'Test Order #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))
@section('page-subtitle', $order->patient->name ?? '')

@section('topbar-actions')
<div class="flex flex-wrap gap-2">
    @if($order->status === 'pending')
    <form method="POST" action="{{ route('branch.orders.collect', [$currentTenant->slug, $order]) }}">
        @csrf
        <button type="submit" class="btn-primary text-sm">Mark Sample Collected</button>
    </form>
    @endif
    @if(in_array($order->status, ['results_ready', 'finalized']))
    <a href="{{ route('branch.orders.report', [$currentTenant->slug, $order]) }}" target="_blank" class="btn-primary text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Download Report
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Order info sidebar --}}
    <div class="space-y-5">
        <div class="glass-card p-6 space-y-3">
            <h4 class="font-medium mb-4" style="color:#1e293b;">Order Summary</h4>
            @php
            $info = [
                'Customer'   => '<a href="' . route('branch.customers.show', [$currentTenant->slug, $order->patient_id]) . '" class="text-indigo-500 hover:underline">' . e($order->patient->name) . '</a>',
                'Created At' => $order->created_at->format('d M Y, H:i'),
                'Status'     => '<span class="badge badge-' . $order->status_color . '">' . $order->status_label . '</span>',
            ];
            @endphp
            @foreach($info as $label => $val)
            <div class="flex items-start justify-between gap-3 text-sm">
                <span class="flex-shrink-0" style="color:#94a3b8;">{{ $label }}</span>
                <span class="text-right">{!! $val !!}</span>
            </div>
            @endforeach

            @if($order->sample_collected_at)
            <div class="flex justify-between text-sm"><span style="color:#94a3b8;">Sample Collected</span><span class="text-right" style="color:#64748b;">{{ $order->sample_collected_at->format('d M Y, H:i') }}</span></div>
            @endif
            @if($order->results_ready_at)
            <div class="flex justify-between text-sm"><span style="color:#94a3b8;">Results Ready</span><span class="text-right" style="color:#64748b;">{{ $order->results_ready_at->format('d M Y, H:i') }}</span></div>
            @endif
            @if($order->finalized_at)
            <div class="flex justify-between text-sm"><span style="color:#94a3b8;">Finalized</span><span class="text-right" style="color:#64748b;">{{ $order->finalized_at->format('d M Y, H:i') }}</span></div>
            @endif

            @if($order->notes)
            <div class="border-t border-black/5 pt-3">
                <p class="text-xs mb-1" style="color:#94a3b8;">Notes</p>
                <p class="text-sm" style="color:#64748b;">{{ $order->notes }}</p>
            </div>
            @endif
        </div>

        @if($order->invoice)
        <div class="glass-card p-6">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-medium" style="color:#1e293b;">Invoice</h4>
                <a href="{{ route('branch.invoices.show', [$currentTenant->slug, $order->invoice]) }}" class="text-indigo-500 text-xs hover:underline">View &rarr;</a>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span style="color:#94a3b8;">Total</span><span class="font-medium" style="color:#1e293b;">{{ money($order->invoice->total) }}</span></div>
                <div class="flex justify-between"><span style="color:#94a3b8;">Status</span>
                    <span class="badge badge-{{ $order->invoice->status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($order->invoice->status) }}</span>
                </div>
            </div>
        </div>
        @endif

        <div class="glass-card p-4" style="background: rgba(99,102,241,0.05); border-color: rgba(99,102,241,0.15);">
            <p class="text-xs" style="color:#64748b;">Results are entered by the main laboratory. You'll be able to download the report once results are ready.</p>
        </div>
    </div>

    {{-- Tests & results (read-only) --}}
    <div class="lg:col-span-2 space-y-4">
        <h4 class="font-medium" style="color:#1e293b;">Tests & Results</h4>

        @php
            $groups = $order->items->groupBy(fn($i) => $i->panel_id ? ($i->panel_name . '#' . $i->panel_id) : '__standalone__');
        @endphp

        @foreach($groups as $groupKey => $items)
        @php $first = $items->first(); $isPanel = $first->panel_id !== null; @endphp

        <div class="space-y-3">
            @if($isPanel)
            <div class="flex items-center gap-2 pt-2">
                <div class="w-7 h-7 rounded-lg bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h5 class="font-semibold text-sm" style="color:#1e293b;">{{ $first->panel_name }}</h5>
                <span class="text-xs" style="color:#94a3b8;">Panel · {{ $items->count() }} tests</span>
            </div>
            @endif

            @php $lastHeader = '__none__'; @endphp
            @foreach($items as $item)
                @if($isPanel && $item->section_header && $item->section_header !== $lastHeader)
                <p class="text-xs uppercase tracking-wider pt-2 pl-1" style="color:#7c3aed;">{{ $item->section_header }}</p>
                @endif
                @php $lastHeader = $item->section_header ?? '__none__'; @endphp

                @php $isText = ($item->result_type ?? $item->testCatalog->result_type ?? 'numeric') === 'text'; @endphp

                <div class="glass-card p-4 {{ $isPanel ? 'ml-1' : '' }}">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-sm truncate" style="color:#1e293b;">{{ $item->testCatalog->name ?? '(removed test)' }}</p>
                            @if(!$isText && ($item->testCatalog->normal_range ?? null))
                            <p class="text-xs" style="color:#94a3b8;">Normal: {{ $item->testCatalog->normal_range }} {{ $item->testCatalog->unit }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($item->result_value)
                            <span class="text-sm font-medium truncate max-w-[180px]" style="color:#059669;">{{ $item->result_value }} {{ $isText ? '' : ($item->testCatalog->unit ?? '') }}</span>
                            @endif
                            <span class="badge badge-{{ $item->status === 'completed' ? 'success' : 'gray' }}">{{ ucfirst($item->status) }}</span>
                        </div>
                    </div>
                    @if($item->remarks)
                    <p class="text-xs mt-2 pl-1" style="color:#94a3b8;">Remarks: {{ $item->remarks }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
@endsection
