@extends('layouts.tenant')

@section('title', 'Branches')
@section('page-title', 'Branch Management')
@section('page-subtitle', 'Manage your laboratory branches and their access')

@section('topbar-actions')
<a href="{{ route('tenant.branches.create', $currentTenant->slug) }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Add Branch
</a>
@endsection

@section('content')
<div class="glass-card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" class="glass-input pl-10 text-sm" placeholder="Search branches...">
        </div>
        <button type="submit" class="btn-primary text-sm">Search</button>
        @if(request('search'))
        <a href="{{ route('tenant.branches.index', $currentTenant->slug) }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Branch</th>
                <th class="hidden md:table-cell">Phone</th>
                <th>Customers</th>
                <th class="hidden lg:table-cell">Orders</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($branches as $branch)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background: linear-gradient(135deg, rgba(16,185,129,0.35), rgba(99,102,241,0.25));">
                            <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">{{ $branch->name }}</p>
                            <p class="text-white/40 text-xs">{{ $branch->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="hidden md:table-cell text-white/60 text-sm">{{ $branch->phone }}</td>
                <td class="text-white text-sm">{{ $branch->patients_count }}</td>
                <td class="hidden lg:table-cell text-white text-sm">{{ $branch->test_orders_count }}</td>
                <td>
                    <span class="badge badge-{{ $branch->is_active ? 'success' : 'gray' }}">
                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('tenant.branches.show', [$currentTenant->slug, $branch]) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10" title="View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <a href="{{ route('tenant.branches.edit', [$currentTenant->slug, $branch]) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('tenant.branches.destroy', [$currentTenant->slug, $branch]) }}"
                              onsubmit="return confirm('Delete this branch?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400/50 hover:text-red-400 transition-colors p-1.5 rounded-lg hover:bg-red-500/10" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-10">
                    <p class="text-white/30">No branches yet.</p>
                    <a href="{{ route('tenant.branches.create', $currentTenant->slug) }}" class="text-indigo-400 text-sm hover:underline mt-1 inline-block">Create your first branch</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($branches->hasPages())
<div class="mt-4">{{ $branches->links() }}</div>
@endif
@endsection
