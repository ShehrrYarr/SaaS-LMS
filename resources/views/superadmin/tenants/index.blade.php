@extends('layouts.superadmin')

@section('title', 'Laboratories')
@section('page-title', 'Laboratories')
@section('page-subtitle', 'Manage all registered labs on the platform')

@section('topbar-actions')
<a href="{{ route('superadmin.tenants.create') }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Add Laboratory
</a>
@endsection

@section('content')
{{-- Filters --}}
<div class="glass-card p-4 mb-6">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="glass-input pl-10" placeholder="Search labs by name, slug, or email...">
        </div>
        <select name="status" class="glass-input sm:w-40">
            <option value="">All Status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <button type="submit" class="btn-primary text-sm flex-shrink-0">Filter</button>
        @if(request('search') || request('status'))
        <a href="{{ route('superadmin.tenants.index') }}" class="btn-secondary text-sm flex-shrink-0">Clear</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Laboratory</th>
                <th>Plan</th>
                <th class="hidden md:table-cell">Contact</th>
                <th>Status</th>
                <th class="hidden lg:table-cell">Created</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-glass">
            @forelse($tenants as $tenant)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(139,92,246,0.2));">
                            <span class="text-white/80 font-semibold text-sm">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-white font-medium text-sm truncate">{{ $tenant->name }}</p>
                            <p class="text-white/40 text-xs">{{ $tenant->slug }}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-purple">{{ $tenant->plan->name ?? '—' }}</span>
                </td>
                <td class="hidden md:table-cell">
                    <p class="text-sm text-white/70">{{ $tenant->email ?? '—' }}</p>
                    <p class="text-xs text-white/40">{{ $tenant->phone ?? '' }}</p>
                </td>
                <td>
                    <span class="badge badge-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'warning' : 'gray') }}">
                        {{ ucfirst($tenant->status) }}
                    </span>
                </td>
                <td class="hidden lg:table-cell text-white/40 text-sm">
                    {{ $tenant->created_at->format('M d, Y') }}
                </td>
                <td>
                    <div class="flex items-center justify-end gap-2" x-data="{ open: false }">
                        <a href="{{ route('superadmin.tenants.show', $tenant) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10"
                           title="View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <a href="{{ route('superadmin.tenants.edit', $tenant) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10"
                           title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>

                        {{-- Status toggle --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false"
                                 class="absolute right-0 mt-1 w-40 glass-card p-1 z-10 shadow-2xl"
                                 x-transition style="display:none">
                                @foreach(['active', 'suspended', 'inactive'] as $status)
                                @if($tenant->status !== $status)
                                <form method="POST" action="{{ route('superadmin.tenants.status', $tenant) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <button type="submit"
                                            class="w-full text-left px-3 py-2 text-sm text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                                        Set {{ ucfirst($status) }}
                                    </button>
                                </form>
                                @endif
                                @endforeach
                                <div class="border-t border-white/10 mt-1 pt-1">
                                    <form method="POST" action="{{ route('superadmin.tenants.destroy', $tenant) }}"
                                          onsubmit="return confirm('Delete this laboratory permanently?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-full text-left px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-12">
                    <p class="text-white/30">No laboratories found.</p>
                    <a href="{{ route('superadmin.tenants.create') }}" class="text-indigo-400 text-sm hover:underline mt-1 inline-block">Add your first lab</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($tenants->hasPages())
<div class="mt-4">{{ $tenants->links() }}</div>
@endif
@endsection
