@extends('layouts.tenant')

@section('title', 'Staff')
@section('page-title', 'Staff Management')
@section('page-subtitle', 'Manage laboratory staff members and their roles')

@section('topbar-actions')
<a href="{{ route('tenant.staff.create', $currentTenant->slug) }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Add Staff
</a>
@endsection

@section('content')
<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th class="hidden md:table-cell">Phone</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $member)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                             style="background: linear-gradient(135deg, rgba(99,102,241,0.4), rgba(139,92,246,0.3));">
                            <span class="text-white text-xs font-bold">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">{{ $member->name }}</p>
                            <p class="text-white/40 text-xs">{{ $member->email }}</p>
                        </div>
                    </div>
                </td>
                <td>
                    @if($member->roles->isNotEmpty())
                        @foreach($member->roles as $role)
                        <span class="badge badge-purple">{{ $role->name }}</span>
                        @endforeach
                    @else
                        <span class="text-white/30 text-sm">No role</span>
                    @endif
                </td>
                <td class="hidden md:table-cell text-white/60 text-sm">{{ $member->phone ?? '—' }}</td>
                <td>
                    <span class="badge badge-{{ $member->is_active ? 'success' : 'gray' }}">
                        {{ $member->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('tenant.staff.edit', [$currentTenant->slug, $member]) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        @if($member->id !== auth()->id())
                        <form method="POST" action="{{ route('tenant.staff.destroy', [$currentTenant->slug, $member]) }}"
                              onsubmit="return confirm('Remove this staff member?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400/50 hover:text-red-400 transition-colors p-1.5 rounded-lg hover:bg-red-500/10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-10">
                    <p class="text-white/30">No staff members yet.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($staff->hasPages())
<div class="mt-4">{{ $staff->links() }}</div>
@endif
@endsection
