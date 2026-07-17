@extends('layouts.branch')

@section('title', 'Dashboard')
@section('page-title', 'Branch Dashboard')
@section('page-subtitle', auth('branch')->user()->name)

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @foreach([
        ['Customers', $stats['customers'], '#6366f1'],
        ['Total Orders', $stats['orders'], '#8b5cf6'],
        ['In Progress', $stats['pending'], '#f59e0b'],
        ['Reports Ready', $stats['reports_ready'], '#10b981'],
        ['Unpaid Balance', number_format($stats['unpaid'], 2), '#ef4444'],
    ] as [$label, $value, $color])
    <div class="glass-card p-5">
        <p class="text-2xl font-bold" style="color: {{ $color }};">{{ $value }}</p>
        <p class="text-xs mt-1" style="color: #94a3b8;">{{ $label }}</p>
    </div>
    @endforeach
</div>

<div class="glass-card p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold" style="color:#1e293b;">Recent Orders</h3>
        <div class="flex gap-2">
            <a href="{{ route('branch.customers.create', $currentTenant->slug) }}" class="btn-secondary text-sm">Register Customer</a>
            <a href="{{ route('branch.orders.create', $currentTenant->slug) }}" class="btn-primary text-sm">New Test Order</a>
        </div>
    </div>

    @forelse($recentOrders as $order)
    <a href="{{ route('branch.orders.show', [$currentTenant->slug, $order]) }}"
       class="flex items-center justify-between py-3 border-b border-black/5 last:border-0 hover:bg-black/[0.02] px-2 rounded-lg transition-colors">
        <div>
            <p class="text-sm font-medium" style="color:#1e293b;">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }} — {{ $order->patient->name ?? '—' }}</p>
            <p class="text-xs" style="color:#94a3b8;">{{ $order->created_at->format('d M Y, H:i') }}</p>
        </div>
        <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
    </a>
    @empty
    <p class="text-sm py-6 text-center" style="color:#94a3b8;">No orders yet. Register a customer and assign tests to get started.</p>
    @endforelse
</div>
@endsection
