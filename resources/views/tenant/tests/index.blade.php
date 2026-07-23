@extends('layouts.tenant')

@section('title', 'Test Catalog')
@section('page-title', 'Test Catalog')
@section('page-subtitle', 'Manage available tests and panels for this laboratory')

@section('topbar-actions')
<a href="{{ route('tenant.tests.create', $currentTenant->slug) }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Add Test
</a>
@endsection

@section('content')
{{-- Filters --}}
<div class="glass-card p-4 mb-5">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" class="glass-input pl-10 text-sm" placeholder="Search tests...">
        </div>
        <select name="category" class="glass-input w-40 text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
        <select name="type" class="glass-input w-36 text-sm">
            <option value="">All Types</option>
            <option value="test" @selected(request('type') === 'test')>Individual Test</option>
            <option value="panel" @selected(request('type') === 'panel')>Panel</option>
        </select>
        <button type="submit" class="btn-primary text-sm">Filter</button>
        @if(request()->hasAny(['search', 'category', 'type']))
        <a href="{{ route('tenant.tests.index', $currentTenant->slug) }}" class="btn-secondary text-sm">Clear</a>
        @endif
    </form>
</div>

<div class="glass-card overflow-hidden">
    <table class="glass-table">
        <thead>
            <tr>
                <th>Test / Panel</th>
                <th>Category</th>
                <th class="hidden md:table-cell">Code</th>
                <th class="hidden lg:table-cell">Normal Range</th>
                <th>Price</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tests as $test)
            <tr>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background: {{ $test->is_panel ? 'rgba(139,92,246,0.2)' : 'rgba(99,102,241,0.2)' }};">
                            <svg class="w-3.5 h-3.5 {{ $test->is_panel ? 'text-purple-400' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($test->is_panel)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                @endif
                            </svg>
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">{{ $test->name }}</p>
                            @if($test->is_panel)
                            <p class="text-purple-400/70 text-xs">Panel · {{ $test->totalTestCount() }} tests</p>
                            @elseif($test->unit)
                            <p class="text-white/30 text-xs">{{ $test->unit }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td><span class="badge badge-info text-xs">{{ $test->category ?? '—' }}</span></td>
                <td class="hidden md:table-cell text-white/50 text-sm">{{ $test->code ?? '—' }}</td>
                <td class="hidden lg:table-cell text-white/50 text-sm">{{ $test->normal_range ?? '—' }}</td>
                <td class="text-white font-medium text-sm">{{ money($test->price) }}</td>
                <td>
                    <span class="badge badge-{{ $test->is_active ? 'success' : 'gray' }}">
                        {{ $test->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('tenant.tests.edit', [$currentTenant->slug, $test]) }}"
                           class="text-white/40 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('tenant.tests.destroy', [$currentTenant->slug, $test]) }}"
                              onsubmit="return confirm('Remove this test from the catalog?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400/50 hover:text-red-400 transition-colors p-1.5 rounded-lg hover:bg-red-500/10">
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
                <td colspan="7" class="text-center py-12">
                    <p class="text-white/30">No tests in the catalog yet.</p>
                    <a href="{{ route('tenant.tests.create', $currentTenant->slug) }}" class="text-indigo-400 text-sm hover:underline mt-1 inline-block">Add your first test</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($tests->hasPages())
<div class="mt-4">{{ $tests->links() }}</div>
@endif
@endsection
