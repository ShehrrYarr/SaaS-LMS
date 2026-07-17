@extends('layouts.tenant')

@section('title', 'Appointment')
@section('page-title', 'Appointment Details')
@section('page-subtitle', $appointment->scheduled_at->format('d M Y, H:i'))

@section('topbar-actions')
<div class="flex gap-2">
    <a href="{{ route('tenant.appointments.edit', [$currentTenant->slug, $appointment]) }}" class="btn-secondary text-sm">Edit</a>
    @unless($appointment->testOrder)
    <a href="{{ route('tenant.orders.create', [$currentTenant->slug, 'appointment_id' => $appointment->id]) }}" class="btn-primary text-sm">Create Test Order</a>
    @endunless
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-3xl">
    <div class="glass-card p-6 space-y-4">
        <h4 class="text-white font-medium">Appointment Info</h4>
        <div class="space-y-3">
            @php $fields = ['Patient' => $appointment->patient->name, 'Scheduled At' => $appointment->scheduled_at->format('d M Y, H:i'), 'Booked By' => $appointment->staff->name ?? '—', 'Status' => ucfirst($appointment->status)]; @endphp
            @foreach($fields as $label => $val)
            <div class="flex justify-between text-sm">
                <span class="text-white/40">{{ $label }}</span>
                <span class="text-white">{{ $val }}</span>
            </div>
            @endforeach
        </div>
        @if($appointment->notes)
        <div class="border-t border-white/10 pt-4">
            <p class="text-white/40 text-xs mb-1">Notes</p>
            <p class="text-white/70 text-sm">{{ $appointment->notes }}</p>
        </div>
        @endif
    </div>

    @if($appointment->testOrder)
    <div class="glass-card p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="text-white font-medium">Test Order</h4>
            <a href="{{ route('tenant.orders.show', [$currentTenant->slug, $appointment->testOrder]) }}" class="text-indigo-400 text-sm hover:underline">View &rarr;</a>
        </div>
        <div class="space-y-2">
            @foreach($appointment->testOrder->items as $item)
            <div class="flex justify-between items-center p-2 rounded-lg bg-white/5 text-sm">
                <span class="text-white">{{ $item->testCatalog->name }}</span>
                <span class="badge badge-{{ match($item->status) { 'completed' => 'success', 'processing' => 'info', default => 'gray' } }}">
                    {{ ucfirst($item->status) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="glass-card p-6 flex flex-col items-center justify-center gap-3 text-center">
        <p class="text-white/30 text-sm">No test order for this appointment yet.</p>
        <a href="{{ route('tenant.orders.create', [$currentTenant->slug, 'appointment_id' => $appointment->id]) }}" class="btn-primary text-sm">Create Test Order</a>
    </div>
    @endif
</div>
@endsection
