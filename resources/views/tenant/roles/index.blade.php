@extends('layouts.tenant')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')
@section('page-subtitle', 'Create custom roles and toggle granular permissions')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

    {{-- Create Role Panel --}}
    <div class="xl:col-span-1">
        <div class="glass-card p-6 sticky top-6">
            <h3 class="text-white font-semibold mb-4">Create Role</h3>
            <form method="POST" action="{{ route('tenant.roles.store', $currentTenant->slug) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Role Name</label>
                    <input type="text" name="name" class="glass-input" placeholder="e.g. Receptionist" required>
                </div>
                <button type="submit" class="btn-primary w-full text-sm">Create Role</button>
            </form>

            @if($roles->isNotEmpty())
            <div class="mt-6 pt-4 border-t border-white/10">
                <h4 class="text-white/50 text-xs uppercase tracking-wider mb-3">Existing Roles</h4>
                <div class="space-y-2">
                    @foreach($roles as $role)
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-white/5">
                        <span class="text-white/70 text-sm">{{ $role->name }}</span>
                        <div class="flex items-center gap-1">
                            <span class="text-white/30 text-xs">{{ $role->permissions->count() }} perms</span>
                            @if($role->users()->count() === 0)
                            <form method="POST" action="{{ route('tenant.roles.destroy', [$currentTenant->slug, $role]) }}"
                                  onsubmit="return confirm('Delete this role?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400/50 hover:text-red-400 ml-2 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Permission Matrix --}}
    <div class="xl:col-span-3">
        @forelse($roles as $role)
        <div class="glass-card p-6 mb-5">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-white font-bold text-lg">{{ $role->name }}</h3>
                    <p class="text-white/40 text-sm">{{ $role->permissions->count() }} permissions assigned</p>
                </div>
                <span class="badge badge-purple">Role</span>
            </div>

            <form method="POST" action="{{ route('tenant.roles.update', [$currentTenant->slug, $role]) }}">
                @csrf @method('PUT')

                @php
                $rolePerms = $role->permissions->pluck('name')->toArray();
                $permGroups = \App\Http\Controllers\Tenant\RoleController::PERMISSION_GROUPS;
                @endphp

                <div class="space-y-5">
                    @foreach($permGroups as $group => $perms)
                    <div class="p-4 rounded-xl" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-white/70 text-sm font-semibold">{{ $group }}</span>
                            <div class="flex-1 h-px bg-white/8"></div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                            @foreach($perms as $perm)
                            <label class="flex items-center gap-2.5 p-2.5 rounded-lg cursor-pointer transition-all hover:bg-white/5"
                                   :class="perms_{{ $role->id }}['{{ $perm }}'] ? 'border border-indigo-500/30 bg-indigo-500/10' : 'border border-white/8'"
                                   x-data
                                   x-bind:class="$store.perms_{{ $role->id }} && $store.perms_{{ $role->id }}['{{ $perm }}'] ? 'border border-indigo-500/30 bg-indigo-500/10' : 'border border-white/8'">
                                <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                       @checked(in_array($perm, $rolePerms))
                                       class="rounded border-white/20 bg-white/5 text-indigo-500 cursor-pointer"
                                       x-data
                                       @change="
                                           if (!Alpine.store('perms_{{ $role->id }}')) Alpine.store('perms_{{ $role->id }}', {});
                                           Alpine.store('perms_{{ $role->id }}')['{{ $perm }}'] = $event.target.checked;
                                       "
                                       x-init="
                                           if (!Alpine.store('perms_{{ $role->id }}')) Alpine.store('perms_{{ $role->id }}', {});
                                           Alpine.store('perms_{{ $role->id }}')['{{ $perm }}'] = {{ in_array($perm, $rolePerms) ? 'true' : 'false' }};
                                       ">
                                <span class="text-xs text-white/60 leading-tight">{{ str_replace('-', ' ', Str::title($perm)) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="flex items-center gap-4 mt-5 pt-4 border-t border-white/10">
                    <button type="submit" class="btn-primary text-sm">Save Permissions</button>
                    <p class="text-white/30 text-xs">Changes take effect immediately.</p>
                </div>
            </form>
        </div>
        @empty
        <div class="glass-card p-12 text-center">
            <svg class="w-12 h-12 text-white/20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <p class="text-white/30 text-sm">No roles yet. Create your first role on the left.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
