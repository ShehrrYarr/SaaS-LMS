@extends('layouts.patient')

@section('title', 'My Invoices')
@section('page-title', 'My Invoices')
@section('page-subtitle', 'View and download your invoices')

@section('content')
<div class="space-y-4">
    @forelse($invoices as $invoice)
    <div class="glass-card p-5">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background: {{ $invoice->status === 'paid' ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.1)' }};">
                    <svg class="w-5 h-5 {{ $invoice->status === 'paid' ? 'text-green-400' : 'text-red-400/70' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-medium">{{ $invoice->invoice_number }}</p>
                    <p class="text-white/40 text-xs">{{ $invoice->created_at->format('d M Y') }} · {{ $invoice->items->count() }} item(s)</p>
                    @if($invoice->status !== 'paid' && $invoice->balance > 0)
                    <p class="text-yellow-400/80 text-xs mt-0.5">Balance due: {{ money($invoice->balance) }}</p>
                    @elseif($invoice->paid_at)
                    <p class="text-green-400/70 text-xs mt-0.5">Paid {{ $invoice->paid_at->format('d M Y') }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <div class="text-right">
                    <p class="text-white font-bold">{{ money($invoice->total) }}</p>
                    <span class="badge badge-{{ $invoice->status_color }} text-xs">{{ ucfirst($invoice->status) }}</span>
                </div>
                <a href="{{ route('patient.invoices.download', [$currentTenant->slug, $invoice]) }}"
                   class="btn-secondary text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    PDF
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="glass-card p-12 text-center">
        <svg class="w-12 h-12 text-white/20 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
        </svg>
        <p class="text-white/30">No invoices yet.</p>
    </div>
    @endforelse

    @if($invoices->hasPages())
    <div class="mt-4">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
