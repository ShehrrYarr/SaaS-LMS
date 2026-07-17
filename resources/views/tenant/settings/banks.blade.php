@extends('layouts.tenant')

@section('title', 'Bank Accounts')
@section('page-title', 'Bank Accounts')
@section('page-subtitle', 'Manage bank accounts used for payment collection')

@section('topbar-actions')
<a href="{{ route('tenant.settings.index', $currentTenant->slug) }}" class="btn-secondary text-sm">← Back to Settings</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- Add bank form --}}
    <div class="lg:col-span-2">
        <div class="glass-card p-6">
            <h3 class="font-semibold text-sm mb-4" style="color:#1e293b;">Add New Bank</h3>
            <form method="POST" action="{{ route('tenant.settings.banks.store', $currentTenant->slug) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Bank Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="glass-input" placeholder="e.g. HBL, Meezan Bank" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Account Title</label>
                    <input type="text" name="account_title" value="{{ old('account_title') }}" class="glass-input" placeholder="e.g. City Lab Pvt Ltd">
                    @error('account_title')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}" class="glass-input" placeholder="e.g. 0123-4567890-01">
                    @error('account_number')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Branch</label>
                    <input type="text" name="branch" value="{{ old('branch') }}" class="glass-input" placeholder="e.g. Gulshan Branch, Karachi">
                    @error('branch')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn-primary w-full">Add Bank</button>
            </form>
        </div>
    </div>

    {{-- Banks list --}}
    <div class="lg:col-span-3 space-y-4">
        @forelse($banks as $bank)
        <div class="glass-card p-5" x-data="{ editing: false }">
            {{-- View mode --}}
            <div x-show="!editing">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background: rgba(99,102,241,0.1);">
                            <svg class="w-5 h-5" style="color:#6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-sm" style="color:#1e293b;">{{ $bank->name }}</p>
                                @if(!$bank->is_active)
                                <span class="badge badge-gray">Inactive</span>
                                @endif
                            </div>
                            @if($bank->account_title)
                            <p class="text-xs mt-0.5" style="color:#64748b;">{{ $bank->account_title }}</p>
                            @endif
                            @if($bank->account_number)
                            <p class="text-xs font-mono mt-0.5" style="color:#64748b;">{{ $bank->account_number }}
                                @if($bank->branch) — {{ $bank->branch }}@endif
                            </p>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <button @click="editing = true" class="btn-secondary text-xs py-1.5 px-3">Edit</button>
                        <form method="POST" action="{{ route('tenant.settings.banks.destroy', [$currentTenant->slug, $bank]) }}"
                              onsubmit="return confirm('Delete this bank?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger text-xs py-1.5 px-3">Delete</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Edit mode --}}
            <div x-show="editing" style="display:none;">
                <form method="POST" action="{{ route('tenant.settings.banks.update', [$currentTenant->slug, $bank]) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label text-xs">Bank Name *</label>
                            <input type="text" name="name" value="{{ $bank->name }}" class="glass-input text-sm" required>
                        </div>
                        <div>
                            <label class="form-label text-xs">Account Title</label>
                            <input type="text" name="account_title" value="{{ $bank->account_title }}" class="glass-input text-sm">
                        </div>
                        <div>
                            <label class="form-label text-xs">Account Number</label>
                            <input type="text" name="account_number" value="{{ $bank->account_number }}" class="glass-input text-sm">
                        </div>
                        <div>
                            <label class="form-label text-xs">Branch</label>
                            <input type="text" name="branch" value="{{ $bank->branch }}" class="glass-input text-sm">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="active_{{ $bank->id }}" value="1" {{ $bank->is_active ? 'checked' : '' }} class="rounded">
                        <label for="active_{{ $bank->id }}" class="text-sm" style="color:#475569;">Active</label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary text-sm py-2 px-4">Save</button>
                        <button type="button" @click="editing = false" class="btn-secondary text-sm py-2 px-4">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="glass-card p-8 text-center">
            <svg class="w-10 h-10 mx-auto mb-3" style="color:#cbd5e1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <p class="text-sm" style="color:#94a3b8;">No banks added yet. Add your first bank account on the left.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
