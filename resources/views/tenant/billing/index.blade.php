@extends('layouts.tenant')

@section('title', 'Billing')
@section('page-title', 'Billing & Invoices')
@section('page-subtitle', 'Track payments and manage invoices')

@section('content')
<div class="glass-card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" class="glass-input pl-10 text-sm" placeholder="Invoice # or patient name…">
        </div>
        <select name="status" class="glass-input w-36 text-sm">
            <option value="">All</option>
            <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
            <option value="partial" @selected(request('status') === 'partial')>Partial</option>
            <option value="paid" @selected(request('status') === 'paid')>Paid</option>
        </select>
        <button type="submit" class="btn-primary text-sm">Filter</button>
        @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('tenant.billing.index', $currentTenant->slug) }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Patient</th>
                <th class="hidden md:table-cell">Subtotal</th>
                <th class="hidden md:table-cell">Discount</th>
                <th>Total</th>
                <th class="hidden lg:table-cell">Paid</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
            <tr>
                <td class="font-mono text-white/70 text-sm">{{ $invoice->invoice_number }}</td>
                <td>
                    <a href="{{ route('tenant.patients.show', [$currentTenant->slug, $invoice->patient_id]) }}"
                       class="text-white text-sm hover:text-indigo-300 transition-colors">{{ $invoice->patient->name ?? '—' }}</a>
                    <p class="text-white/30 text-xs">{{ $invoice->created_at->format('d M Y') }}</p>
                </td>
                <td class="hidden md:table-cell text-white/50 text-sm">{{ money($invoice->subtotal) }}</td>
                <td class="hidden md:table-cell text-white/50 text-sm">
                    {{ $invoice->discount > 0 ? '-' . money($invoice->discount) : '—' }}
                </td>
                <td class="text-white font-semibold text-sm">{{ money($invoice->total) }}</td>
                <td class="hidden lg:table-cell text-green-400 text-sm">{{ $invoice->amount_paid > 0 ? money($invoice->amount_paid) : '—' }}</td>
                <td>
                    <span class="badge badge-{{ $invoice->status_color }}">{{ ucfirst($invoice->status) }}</span>
                </td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('tenant.billing.show', [$currentTenant->slug, $invoice]) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <a href="{{ route('tenant.billing.pdf', [$currentTenant->slug, $invoice]) }}"
                           class="text-white/40 hover:text-green-400 transition-colors p-1.5 rounded-lg hover:bg-green-500/10" title="Download PDF">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-12 text-white/30">No invoices yet. They are created automatically with test orders.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($invoices->hasPages())
<div class="mt-4">{{ $invoices->links() }}</div>
@endif
@endsection
