@extends('layouts.branch')

@section('title', 'Customers')
@section('page-title', 'My Customers')
@section('page-subtitle', 'Customers registered by this branch')

@section('topbar-actions')
<a href="{{ route('branch.customers.create', $currentTenant->slug) }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Register Customer
</a>
@endsection

@section('content')
<div class="glass-card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-48">
            <input type="text" name="search" value="{{ request('search') }}" class="glass-input text-sm" placeholder="Search by name, email, code, phone...">
        </div>
        <button type="submit" class="btn-primary text-sm">Search</button>
        @if(request('search'))
        <a href="{{ route('branch.customers.index', $currentTenant->slug) }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th class="hidden sm:table-cell">Code</th>
                <th class="hidden md:table-cell">Phone</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($patients as $patient)
            <tr>
                <td>
                    <a href="{{ route('branch.customers.show', [$currentTenant->slug, $patient]) }}" class="text-sm font-medium hover:underline" style="color:#1e293b;">{{ $patient->name }}</a>
                    <p class="text-xs" style="color:#94a3b8;">{{ $patient->email }}</p>
                </td>
                <td class="hidden sm:table-cell text-sm font-mono" style="color:#64748b;">{{ $patient->patient_code }}</td>
                <td class="hidden md:table-cell text-sm" style="color:#64748b;">{{ $patient->phone ?? '—' }}</td>
                <td>
                    <span class="badge badge-{{ $patient->is_active ? 'success' : 'gray' }}">
                        {{ $patient->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('branch.orders.create', [$currentTenant->slug, 'patient_id' => $patient->id]) }}"
                           class="btn-secondary text-xs py-1.5 px-3">Assign Tests</a>
                        <a href="{{ route('branch.customers.edit', [$currentTenant->slug, $patient]) }}"
                           class="text-xs py-1.5 px-3 rounded-lg transition-colors" style="color:#64748b;"
                           onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">Edit</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-10">
                    <p style="color:#94a3b8;">No customers yet.</p>
                    <a href="{{ route('branch.customers.create', $currentTenant->slug) }}" class="text-indigo-500 text-sm hover:underline mt-1 inline-block">Register your first customer</a>
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
