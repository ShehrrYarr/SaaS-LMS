@extends('layouts.tenant')

@section('title', 'Test Orders')
@section('page-title', 'Test Orders')
@section('page-subtitle', 'Manage and track all laboratory test orders')

@section('topbar-actions')
<a href="{{ route('tenant.orders.create', $currentTenant->slug) }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    New Order
</a>
@endsection

@section('content')
<div class="glass-card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" class="glass-input pl-10 text-sm" placeholder="Search patient name…">
        </div>
        <select name="status" class="glass-input w-48 text-sm">
            <option value="">All Statuses</option>
            @foreach(['pending', 'sample_collected', 'processing', 'results_ready', 'finalized', 'cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary text-sm">Filter</button>
        @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('tenant.orders.index', $currentTenant->slug) }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Patient</th>
                <th class="hidden md:table-cell">Tests</th>
                <th class="hidden lg:table-cell">Created</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td class="font-mono text-white/70 text-sm">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td>
                    <a href="{{ route('tenant.patients.show', [$currentTenant->slug, $order->patient_id]) }}"
                       class="text-white text-sm font-medium hover:text-indigo-300 transition-colors">
                        {{ $order->patient->name ?? '—' }}
                    </a>
                    @if($order->branch)
                    <p class="mt-0.5"><span class="badge badge-success text-xs">{{ $order->branch->name }}</span></p>
                    @endif
                </td>
                <td class="hidden md:table-cell text-white/50 text-sm">{{ $order->items_count ?? $order->items->count() }} test(s)</td>
                <td class="hidden lg:table-cell text-white/40 text-sm">{{ $order->created_at->format('d M Y') }}</td>
                <td>
                    <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
                </td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('tenant.orders.show', [$currentTenant->slug, $order]) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        @if(in_array($order->status, ['results_ready', 'finalized']))
                        <a href="{{ route('tenant.orders.report', [$currentTenant->slug, $order]) }}"
                           class="text-white/40 hover:text-green-400 transition-colors p-1.5 rounded-lg hover:bg-green-500/10" title="Download Report">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-12 text-white/30">No test orders yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($orders->hasPages())
<div class="mt-4">{{ $orders->links() }}</div>
@endif
@endsection
