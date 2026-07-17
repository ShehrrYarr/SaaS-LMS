@extends('layouts.tenant')

@section('title', 'Appointments')
@section('page-title', 'Appointments')
@section('page-subtitle', 'Schedule and manage patient appointments')

@section('topbar-actions')
<a href="{{ route('tenant.appointments.create', $currentTenant->slug) }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Book Appointment
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
        <input type="date" name="date" value="{{ request('date') }}" class="glass-input w-44 text-sm">
        <select name="status" class="glass-input w-40 text-sm">
            <option value="">All Statuses</option>
            @foreach(['scheduled', 'arrived', 'completed', 'cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary text-sm">Filter</button>
        @if(request()->hasAny(['search', 'date', 'status']))
        <a href="{{ route('tenant.appointments.index', $currentTenant->slug) }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Patient</th>
                <th>Date & Time</th>
                <th class="hidden md:table-cell">Booked By</th>
                <th class="hidden lg:table-cell">Notes</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $apt)
            <tr>
                <td>
                    <a href="{{ route('tenant.patients.show', [$currentTenant->slug, $apt->patient_id]) }}"
                       class="text-white text-sm font-medium hover:text-indigo-300 transition-colors">
                        {{ $apt->patient->name ?? '—' }}
                    </a>
                    <p class="text-white/30 text-xs">{{ $apt->patient->patient_code ?? '' }}</p>
                </td>
                <td>
                    <p class="text-white text-sm">{{ $apt->scheduled_at->format('d M Y') }}</p>
                    <p class="text-white/40 text-xs">{{ $apt->scheduled_at->format('H:i') }}</p>
                </td>
                <td class="hidden md:table-cell text-white/50 text-sm">{{ $apt->staff->name ?? '—' }}</td>
                <td class="hidden lg:table-cell text-white/50 text-sm">{{ Str::limit($apt->notes, 40) ?? '—' }}</td>
                <td>
                    <span class="badge badge-{{ $apt->status_color }}">{{ ucfirst($apt->status) }}</span>
                </td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        @if($apt->testOrder)
                        <a href="{{ route('tenant.orders.show', [$currentTenant->slug, $apt->testOrder]) }}"
                           class="text-white/40 hover:text-indigo-400 transition-colors p-1.5 rounded-lg hover:bg-indigo-500/10" title="View Order">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </a>
                        @else
                        <a href="{{ route('tenant.orders.create', [$currentTenant->slug, 'appointment_id' => $apt->id]) }}"
                           class="text-white/40 hover:text-green-400 transition-colors p-1.5 rounded-lg hover:bg-green-500/10" title="Create Test Order">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </a>
                        @endif
                        <a href="{{ route('tenant.appointments.edit', [$currentTenant->slug, $apt]) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-12 text-white/30">No appointments found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($appointments->hasPages())
<div class="mt-4">{{ $appointments->links() }}</div>
@endif
@endsection
