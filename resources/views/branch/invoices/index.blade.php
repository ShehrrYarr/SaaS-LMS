@extends('layouts.branch')

@section('title', 'Invoices')
@section('page-title', 'Invoices')
@section('page-subtitle', 'Invoices for customers of this branch')

@section('content')
<div class="glass-card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-48">
            <input type="text" name="search" value="{{ request('search') }}" class="glass-input text-sm" placeholder="Search by invoice number or customer...">
        </div>
        <select name="status" class="glass-input w-36 text-sm">
            <option value="">All Statuses</option>
            @foreach(['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid'] as $val => $label)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary text-sm">Filter</button>
        @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('branch.invoices.index', $currentTenant->slug) }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th class="hidden md:table-cell">Date</th>
                <th>Total</th>
                <th class="hidden sm:table-cell">Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
            <tr>
                <td>
                    <a href="{{ route('branch.invoices.show', [$currentTenant->slug, $invoice]) }}"
                       class="text-sm font-mono font-medium hover:underline" style="color:#1e293b;">{{ $invoice->invoice_number }}</a>
                </td>
                <td class="text-sm" style="color:#64748b;">{{ $invoice->patient->name ?? '—' }}</td>
                <td class="hidden md:table-cell text-sm" style="color:#94a3b8;">{{ $invoice->created_at->format('d M Y') }}</td>
                <td class="text-sm font-medium" style="color:#1e293b;">{{ money($invoice->total) }}</td>
                <td class="hidden sm:table-cell text-sm" style="color: {{ $invoice->balance > 0 ? '#d97706' : '#16a34a' }};">{{ money($invoice->balance) }}</td>
                <td><span class="badge badge-{{ $invoice->status_color }}">{{ ucfirst($invoice->status) }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-10">
                    <p style="color:#94a3b8;">No invoices yet. Invoices are generated automatically when you create a test order.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($invoices->hasPages())
<div class="mt-4">{{ $invoices->links() }}</div>
@endif
@endsection
