@extends('layouts.tenant')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')
@section('page-subtitle', 'Manage roles and control what each one can do')

@section('topbar-actions')
<button type="button" onclick="document.getElementById('create-role-modal').style.display='flex'"
        class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    New Role
</button>
@endsection

@section('content')
<div x-data="rolesApp()">

    {{-- Role cards grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($roles as $role)
        @php
            $isSystem   = $role->name === 'Lab Admin';
            $permCount  = $role->permissions->count();
            $userCount  = $userCounts[$role->id] ?? 0;
            $roleData   = Js::from([
                'id'         => $role->id,
                'name'       => $role->name,
                'is_system'  => $isSystem,
                'perms'      => $role->permissions->pluck('name')->values()->toArray(),
                'user_count' => $userCount,
                'update_url' => route('tenant.roles.update',  [$currentTenant->slug, $role]),
                'delete_url' => route('tenant.roles.destroy', [$currentTenant->slug, $role]),
            ]);
        @endphp
        <button type="button"
                @click="open({{ $roleData }})"
                class="glass-card-hover p-6 text-left w-full transition-all">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="{{ $isSystem ? 'background:rgba(99,102,241,0.15)' : 'background:rgba(255,255,255,0.07)' }}">
                    @if($isSystem)
                    <svg class="w-5 h-5" style="color:#6366f1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    @else
                    <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    @endif
                </div>
                @if($isSystem)
                <span class="badge badge-purple flex items-center gap-1 flex-shrink-0">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    System
                </span>
                @else
                <span class="badge badge-gray">Custom</span>
                @endif
            </div>

            <h3 class="text-white font-bold text-base mb-1">{{ $role->name }}</h3>
            <p class="text-white/40 text-sm">
                {{ $isSystem ? 'All permissions' : $permCount . ' permission' . ($permCount === 1 ? '' : 's') }}
                @if($userCount > 0)
                 · {{ $userCount }} {{ Str::plural('staff', $userCount) }}
                @endif
            </p>

            {{-- Permission group dots --}}
            @if(!$isSystem)
            <div class="flex flex-wrap gap-1.5 mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.06);">
                @foreach($permGroups as $group => $perms)
                @php $hasAny = $role->permissions->whereIn('name', $perms)->isNotEmpty(); @endphp
                <span class="text-xs px-2 py-0.5 rounded-full"
                      style="{{ $hasAny ? 'background:rgba(99,102,241,0.15); color:#a5b4fc;' : 'background:rgba(255,255,255,0.04); color:#475569;' }}">
                    {{ $group }}
                </span>
                @endforeach
            </div>
            @else
            <div class="flex items-center gap-1.5 mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.06);">
                <span class="text-xs" style="color:#6ee7b7;">All {{ count(array_merge(...array_values($permGroups))) }} permissions granted</span>
            </div>
            @endif
        </button>
        @empty
        <div class="col-span-3 glass-card p-12 text-center">
            <p class="text-white/30">No roles yet. Click <strong class="text-white/50">New Role</strong> to create one.</p>
        </div>
        @endforelse
    </div>

    {{-- ── Permission modal — teleported to <body> so it sits above the topbar
         and is independent of the scrollable <main> container ──────────── --}}
    <template x-teleport="body">
    <div x-show="selected"
         class="fixed inset-0 z-[500] flex items-center justify-center p-4"
         style="background: rgba(0,0,0,0.45); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display:none;"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="close()">

        <div class="app-modal w-full max-w-2xl max-h-[88vh] flex flex-col rounded-2xl overflow-hidden"
             style="background: var(--modal-bg, rgba(255,255,255,0.97)); color: var(--modal-text, #1e293b); border: 1px solid var(--modal-border, rgba(0,0,0,0.09)); box-shadow: 0 25px 60px rgba(0,0,0,0.35);"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.stop>

            {{-- Modal header --}}
            <div class="flex items-start justify-between gap-4 p-6 flex-shrink-0"
                 style="border-bottom: 1px solid var(--modal-divider, rgba(0,0,0,0.07));">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-bold text-lg" x-text="selected?.name"></h3>
                        <span x-show="selected?.is_system" class="badge badge-purple flex items-center gap-1 text-xs">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            System — read only
                        </span>
                    </div>
                    <p class="text-sm mt-0.5" style="opacity: 0.55;"
                       x-text="selected?.is_system
                           ? 'This role always has every permission and cannot be changed.'
                           : selected?.perms.length + ' permission' + (selected?.perms.length === 1 ? '' : 's') + ' assigned'">
                    </p>
                </div>
                <button type="button" @click="close()"
                        class="transition-colors flex-shrink-0 mt-0.5" style="opacity: 0.4; color: inherit;"
                        onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.4'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal body (scrollable) --}}
            <div class="overflow-y-auto flex-1 p-6 space-y-4">

                {{-- ── System role (Lab Admin) — read-only view ── --}}
                <div x-show="selected?.is_system">
                    @foreach($permGroups as $group => $perms)
                    <div class="rounded-xl p-4 mb-3" style="background: var(--modal-surface, rgba(0,0,0,0.04)); border: 1px solid var(--modal-divider, rgba(0,0,0,0.07));">
                        <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="opacity: 0.55;">{{ $group }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($perms as $perm)
                            <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg"
                                 style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2)">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#34d399" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs" style="color:#6ee7b7;">{{ str_replace('-', ' ', Str::title($perm)) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- ── Custom role — editable form ── --}}
                <div x-show="!selected?.is_system">
                    <form :action="selected?.update_url" method="POST" id="perm-form">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">

                        <div class="space-y-3">
                            @foreach($permGroups as $group => $perms)
                            <div class="rounded-xl p-4" style="background: var(--modal-surface, rgba(0,0,0,0.04)); border: 1px solid var(--modal-divider, rgba(0,0,0,0.07));">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-xs font-semibold uppercase tracking-wider" style="opacity: 0.55;">{{ $group }}</span>
                                    <div class="flex-1 h-px" style="background: var(--modal-divider, rgba(0,0,0,0.07));"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($perms as $perm)
                                    <label class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg cursor-pointer transition-all select-none"
                                           :style="selected?.perms.includes('{{ $perm }}')
                                               ? 'background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.3);'
                                               : 'background: var(--modal-surface, rgba(0,0,0,0.04)); border: 1px solid var(--modal-divider, rgba(0,0,0,0.07));'">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $perm }}"
                                               :checked="selected?.perms.includes('{{ $perm }}')"
                                               @change="togglePerm('{{ $perm }}')"
                                               style="accent-color:#6366f1; cursor:pointer; width:14px; height:14px; flex-shrink:0;">
                                        <span class="text-xs leading-tight"
                                              :style="selected?.perms.includes('{{ $perm }}') ? 'color:#c7d2fe;' : 'opacity: 0.6;'">
                                            {{ str_replace('-', ' ', Str::title($perm)) }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal footer --}}
            <div class="flex items-center justify-between gap-3 p-5 flex-shrink-0"
                 style="border-top: 1px solid var(--modal-divider, rgba(0,0,0,0.07));">

                {{-- Left: Save (custom only) --}}
                <div x-show="!selected?.is_system">
                    <button type="submit" form="perm-form" class="btn-primary text-sm">Save Permissions</button>
                </div>
                <div x-show="selected?.is_system">
                    <span class="text-sm" style="opacity: 0.35;">Read-only — system role</span>
                </div>

                {{-- Right: Cancel + Delete --}}
                <div class="flex items-center gap-2">
                    <button type="button" @click="close()" class="btn-secondary text-sm">Close</button>
                    <form x-show="!selected?.is_system && selected?.user_count === 0"
                          :action="selected?.delete_url" method="POST"
                          @submit.prevent="confirmDelete($event)">
                        @csrf
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                                class="text-sm px-3 py-1.5 rounded-lg transition-colors"
                                style="color:#f87171; border:1px solid rgba(248,113,113,0.3);"
                                onmouseover="this.style.background='rgba(248,113,113,0.1)'"
                                onmouseout="this.style.background=''">
                            Delete Role
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    </template>{{-- end x-teleport --}}
</div>{{-- end x-data --}}

@push('modals')
{{-- ── Create Role modal (plain JS, teleported to body via stack) ─────── --}}
<div id="create-role-modal"
     class="fixed inset-0 z-[500] items-center justify-center p-4"
     style="background: rgba(0,0,0,0.45); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display:none;"
     onclick="if(event.target===this) this.style.display='none'">
    <div class="app-modal w-full max-w-md rounded-2xl p-6"
         style="background: var(--modal-bg, rgba(255,255,255,0.97)); color: var(--modal-text, #1e293b); border: 1px solid var(--modal-border, rgba(0,0,0,0.09)); box-shadow: 0 25px 60px rgba(0,0,0,0.35);"
         onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-bold text-lg">Create New Role</h3>
            <button type="button"
                    onclick="document.getElementById('create-role-modal').style.display='none'"
                    class="transition-colors" style="opacity: 0.4; color: inherit;"
                    onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.4'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('tenant.roles.store', $currentTenant->slug) }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Role Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" class="glass-input" placeholder="e.g. Receptionist, Lab Technician" required autofocus>
                <p class="text-xs mt-1" style="opacity: 0.4;">You can assign permissions after creating the role.</p>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="btn-primary flex-1 text-sm">Create Role</button>
                <button type="button"
                        onclick="document.getElementById('create-role-modal').style.display='none'"
                        class="btn-secondary text-sm">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endpush

<script>
function rolesApp() {
    return {
        selected: null,

        open(role) {
            // Deep-copy so user can toggle perms without mutating the card data
            this.selected = { ...role, perms: [...role.perms] };
        },

        close() {
            this.selected = null;
        },

        togglePerm(perm) {
            if (!this.selected) return;
            const idx = this.selected.perms.indexOf(perm);
            if (idx >= 0) {
                this.selected.perms.splice(idx, 1);
            } else {
                this.selected.perms.push(perm);
            }
        },

        confirmDelete(event) {
            if (confirm('Delete the role "' + this.selected?.name + '"? This cannot be undone.')) {
                event.target.submit();
            }
        },
    };
}
</script>
@endsection
