@extends('layouts.tenant')

@section('title', 'Patients')
@section('page-title', 'Patients')
@section('page-subtitle', 'All registered patients for this laboratory')

@section('topbar-actions')
<a href="{{ route('tenant.patients.create', $currentTenant->slug) }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Register Patient
</a>
@endsection

@section('content')
<div class="glass-card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" class="glass-input pl-10 text-sm" placeholder="Name, code, email or phone…">
        </div>
        <select name="gender" class="glass-input w-32 text-sm">
            <option value="">Any Gender</option>
            <option value="male" @selected(request('gender') === 'male')>Male</option>
            <option value="female" @selected(request('gender') === 'female')>Female</option>
            <option value="other" @selected(request('gender') === 'other')>Other</option>
        </select>
        <select name="status" class="glass-input w-32 text-sm">
            <option value="">Any Status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <button type="submit" class="btn-primary text-sm">Filter</button>
        @if(request()->hasAny(['search', 'gender', 'status']))
        <a href="{{ route('tenant.patients.index', $currentTenant->slug) }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Patient</th>
                <th class="hidden sm:table-cell">Code</th>
                <th class="hidden md:table-cell">Phone</th>
                <th class="hidden lg:table-cell">DOB / Age</th>
                <th class="hidden lg:table-cell">Blood Group</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($patients as $patient)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-indigo-400 text-xs font-bold">{{ strtoupper(substr($patient->name, 0, 2)) }}</span>
                        </div>
                        <div>
                            <a href="{{ route('tenant.patients.show', [$currentTenant->slug, $patient]) }}" class="text-white text-sm font-medium hover:text-indigo-300 transition-colors">{{ $patient->name }}</a>
                            <p class="text-white/30 text-xs">
                                {{ $patient->email }}
                                @if($patient->branch)
                                <span class="badge badge-success text-xs">{{ $patient->branch->name }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </td>
                <td class="hidden sm:table-cell text-white/50 text-sm font-mono">{{ $patient->patient_code }}</td>
                <td class="hidden md:table-cell text-white/50 text-sm">{{ $patient->phone ?? '—' }}</td>
                <td class="hidden lg:table-cell text-white/50 text-sm">
                    {{ $patient->dob ? $patient->dob->format('d M Y') . ' (' . $patient->age . 'y)' : '—' }}
                </td>
                <td class="hidden lg:table-cell">
                    @if($patient->blood_group)
                    <span class="badge badge-danger">{{ $patient->blood_group }}</span>
                    @else —
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $patient->is_active ? 'success' : 'gray' }}">
                        {{ $patient->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('tenant.patients.show', [$currentTenant->slug, $patient]) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10"
                           title="View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <a href="{{ route('tenant.patients.edit', [$currentTenant->slug, $patient]) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10"
                           title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <a href="{{ route('tenant.appointments.create', [$currentTenant->slug, 'patient_id' => $patient->id]) }}"
                           class="text-white/40 hover:text-indigo-400 transition-colors p-1.5 rounded-lg hover:bg-indigo-500/10"
                           title="Book Appointment">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-12">
                    <p class="text-white/30">No patients registered yet.</p>
                    <a href="{{ route('tenant.patients.create', $currentTenant->slug) }}" class="text-indigo-400 text-sm hover:underline mt-1 inline-block">Register the first patient</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($patients->hasPages())
<div class="mt-4">{{ $patients->links() }}</div>
@endif
@endsection
