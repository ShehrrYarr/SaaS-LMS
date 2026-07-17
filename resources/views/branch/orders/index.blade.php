@extends('layouts.branch')

@section('title', 'Test Orders')
@section('page-title', 'Test Orders')
@section('page-subtitle', 'Orders created by this branch')

@section('topbar-actions')
<a href="{{ route('branch.orders.create', $currentTenant->slug) }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    New Order
</a>
@endsection

@section('content')
<div class="glass-card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-48">
            <input type="text" name="search" value="{{ request('search') }}" class="glass-input text-sm" placeholder="Search by customer name...">
        </div>
        <select name="status" class="glass-input w-44 text-sm">
            <option value="">All Statuses</option>
            @foreach(['pending' => 'Pending', 'sample_collected' => 'Sample Collected', 'processing' => 'Processing', 'results_ready' => 'Results Ready', 'finalized' => 'Finalized'] as $val => $label)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary text-sm">Filter</button>
        @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('branch.orders.index', $currentTenant->slug) }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th class="hidden lg:table-cell">Date</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td class="font-mono text-sm" style="color:#64748b;">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td>
                    <a href="{{ route('branch.orders.show', [$currentTenant->slug, $order]) }}" class="text-sm font-medium hover:underline" style="color:#1e293b;">
                        {{ $order->patient->name ?? '—' }}
                    </a>
                </td>
                <td class="hidden lg:table-cell text-sm" style="color:#94a3b8;">{{ $order->created_at->format('d M Y') }}</td>
                <td><span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span></td>
                <td>
                    <div class="flex items-center justify-end gap-2">
                        @if(in_array($order->status, ['results_ready', 'finalized']))
                        <a href="{{ route('branch.orders.report', [$currentTenant->slug, $order]) }}" target="_blank"
                           class="btn-secondary text-xs py-1.5 px-3">Report</a>
                        @endif
                        <a href="{{ route('branch.orders.show', [$currentTenant->slug, $order]) }}"
                           class="text-xs py-1.5 px-3 rounded-lg" style="color:#64748b;">View</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-10">
                    <p style="color:#94a3b8;">No orders yet.</p>
                    <a href="{{ route('branch.orders.create', $currentTenant->slug) }}" class="text-indigo-500 text-sm hover:underline mt-1 inline-block">Create your first order</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($orders->hasPages())
<div class="mt-4">{{ $orders->links() }}</div>
@endif
@endsection
