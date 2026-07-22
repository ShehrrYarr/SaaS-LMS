@extends('layouts.patient')

@section('title', 'My Reports')
@section('page-title', 'My Reports')
@section('page-subtitle', 'Download your test result reports')

@section('content')
<div class="space-y-4">
    @forelse($orders as $order)
    <div class="glass-card p-5">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background: {{ in_array($order->status, ['results_ready','finalized']) ? 'rgba(34,197,94,0.2)' : 'rgba(255,255,255,0.06)' }};">
                    <svg class="w-5 h-5 {{ in_array($order->status, ['results_ready','finalized']) ? 'text-green-400' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-medium">Order #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-white/40 text-xs">{{ $order->created_at->format('d M Y') }} · {{ $order->items->count() }} test(s)</p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach($order->items->take(3) as $item)
                        <span class="text-white/30 text-xs">{{ $item->testCatalog->name }}</span>{{ !$loop->last && $loop->index < 2 ? ',' : '' }}
                        @endforeach
                        @if($order->items->count() > 3)
                        <span class="text-white/30 text-xs">+{{ $order->items->count() - 3 }} more</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
                @if(in_array($order->status, ['results_ready', 'finalized']))
                @php $invoicePaid = $order->invoice && $order->invoice->status === 'paid'; @endphp
                <a href="{{ route('patient.reports.download', [$currentTenant->slug, $order]) }}"
                   class="flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-xl transition-all"
                   style="{{ $invoicePaid ? 'background:rgba(99,102,241,0.2); color:#a5b4fc;' : 'background:rgba(255,255,255,0.06); color:#94a3b8;' }}"
                   title="{{ $invoicePaid ? 'Download report PDF' : 'Pay your invoice to download' }}">
                    @if($invoicePaid)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                    @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Pay to Download
                    @endif
                </a>
                @else
                <span class="text-white/30 text-sm">Not ready yet</span>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="glass-card p-12 text-center">
        <svg class="w-12 h-12 text-white/20 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-white/30">No test reports yet.</p>
    </div>
    @endforelse

    @if($orders->hasPages())
    <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
