@extends('layouts.tenant')

@section('title', 'Settings')
@section('page-title', 'Lab Settings')
@section('page-subtitle', 'Configure email, PDF branding, and laboratory information')

@section('content')
<div class="max-w-3xl space-y-6" x-data="{ tab: '{{ $errors->hasAny(['current_password', 'password']) ? 'account' : 'smtp' }}' }">

    {{-- Tabs --}}
    <div class="glass-card p-1 flex gap-1">
        @php
            $tabs = ['smtp' => 'Email / SMTP', 'branding' => 'PDF Branding'];
            if (auth()->user()->can('manage-settings')) {
                $tabs['account'] = 'Account Password';
            }
        @endphp
        @foreach($tabs as $key => $label)
        <button @click="tab = '{{ $key }}'"
                :style="tab === '{{ $key }}'
                    ? 'background:rgba(99,102,241,0.12); color:#6366f1;'
                    : 'color:#64748b;'"
                class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium transition-all">
            {{ $label }}
        </button>
        @endforeach
        <a href="{{ route('tenant.settings.banks', $currentTenant->slug) }}"
           class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium text-center transition-all"
           style="color:#64748b;" onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">
            Bank Accounts
        </a>
        <a href="{{ route('tenant.settings.template-builder', $currentTenant->slug) }}"
           class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium text-center transition-all"
           style="color:#64748b;" onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">
            PDF Builder
        </a>
        <a href="{{ route('tenant.settings.domain', $currentTenant->slug) }}"
           class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium text-center transition-all"
           style="color:#64748b;" onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">
            Custom Domain
        </a>
        <a href="{{ route('tenant.settings.appearance', $currentTenant->slug) }}"
           class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium text-center transition-all"
           style="color:#64748b;" onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">
            Appearance
        </a>
    </div>

    {{-- SMTP Tab --}}
    <div x-show="tab === 'smtp'" x-transition class="glass-card p-8">
        <div class="mb-6">
            <h3 class="font-semibold" style="color:#1e293b;">Email Configuration</h3>
            <p class="text-sm mt-1" style="color:#64748b;">Configure your lab's outgoing email server. Patient credentials and notifications will be sent using these settings.</p>
        </div>

        <form method="POST" action="{{ route('tenant.settings.smtp', $currentTenant->slug) }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="smtp_host"
                           value="{{ old('smtp_host', $tenant->getSetting('smtp_host')) }}"
                           class="glass-input" placeholder="smtp.gmail.com" required>
                </div>
                <div>
                    <label class="form-label">Port</label>
                    <input type="number" name="smtp_port"
                           value="{{ old('smtp_port', $tenant->getSetting('smtp_port', 587)) }}"
                           class="glass-input" placeholder="587" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Username / Email</label>
                    <input type="text" name="smtp_username"
                           value="{{ old('smtp_username', $tenant->getSetting('smtp_username')) }}"
                           class="glass-input" placeholder="lab@gmail.com" required>
                </div>
                <div>
                    <label class="form-label">Password / App Key</label>
                    <input type="password" name="smtp_password"
                           value="{{ old('smtp_password', $tenant->getSetting('smtp_password')) }}"
                           class="glass-input" placeholder="••••••••" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Encryption</label>
                    <select name="smtp_encryption" class="glass-input">
                        @foreach(['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'None'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('smtp_encryption', $tenant->getSetting('smtp_encryption', 'tls')) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">From Address</label>
                    <input type="email" name="mail_from_address"
                           value="{{ old('mail_from_address', $tenant->getSetting('mail_from_address', $tenant->email)) }}"
                           class="glass-input" required>
                </div>
                <div>
                    <label class="form-label">From Name</label>
                    <input type="text" name="mail_from_name"
                           value="{{ old('mail_from_name', $tenant->getSetting('mail_from_name', $tenant->name)) }}"
                           class="glass-input" required>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4" style="border-top: 1px solid rgba(0,0,0,0.08);">
                <button type="submit" class="btn-primary">Save SMTP Settings</button>
                <form method="POST" action="{{ route('tenant.settings.smtp.test', $currentTenant->slug) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Test Connection</button>
                </form>
            </div>
        </form>
    </div>

    {{-- Branding Tab --}}
    <div x-show="tab === 'branding'" x-transition class="glass-card p-8" style="display:none">
        <div class="mb-6">
            <h3 class="font-semibold" style="color:#1e293b;">PDF Report & Invoice Branding</h3>
            <p class="text-sm mt-1" style="color:#64748b;">Upload up to 5 logos and drag them anywhere in the PDF Builder. The signature appears on all generated PDFs.</p>
        </div>

        @php
        // Load existing logos; migrate old single logo for labs that haven't switched yet
        $existingLogos = json_decode($tenant->getSetting('report_logos', '[]'), true) ?: [];
        if (empty($existingLogos) && $oldPath = $tenant->getSetting('report_logo')) {
            $existingLogos = [['key' => 'logo_primary', 'label' => 'Logo', 'path' => $oldPath]];
        }
        $existingLogosForJs = collect($existingLogos)->map(fn($lg) => array_merge($lg, [
            'url' => !empty($lg['path']) && file_exists(storage_path('app/public/' . $lg['path']))
                ? asset('storage/' . $lg['path']) : null,
        ]))->values()->toArray();
        @endphp

        <form method="POST" action="{{ route('tenant.settings.branding', $currentTenant->slug) }}"
              enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Multi-logo manager --}}
            <div x-data="logoManager({{ json_encode($existingLogosForJs) }})">
                <div class="flex items-center justify-between mb-3">
                    <label class="form-label mb-0">Logos <span class="text-white/30 font-normal text-xs">(up to 5)</span></label>
                    <button type="button" x-show="slots.length < 5" @click="add()"
                            class="text-xs font-medium px-3 py-1.5 rounded-lg transition-all"
                            style="color:#6366f1; background:rgba(99,102,241,0.08);"
                            onmouseover="this.style.background='rgba(99,102,241,0.15)'"
                            onmouseout="this.style.background='rgba(99,102,241,0.08)'">
                        + Add Logo
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(s, i) in slots" :key="s.tempId">
                        <div class="flex items-start gap-3 p-3 rounded-xl" style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.09);">
                            {{-- Thumbnail --}}
                            <div class="w-20 h-12 rounded-lg flex-shrink-0 flex items-center justify-center overflow-hidden"
                                 style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08);">
                                <img x-show="s.preview || s.existing" :src="s.preview || s.existing"
                                     class="w-full h-full object-contain">
                                <span x-show="!s.preview && !s.existing" class="text-xs" style="color:rgba(255,255,255,0.2);">No image</span>
                            </div>

                            {{-- Fields --}}
                            <div class="flex-1 space-y-2 min-w-0">
                                <input type="hidden" :name="`logo_${i}_key`" :value="s.key">
                                <input type="text" :name="`logo_${i}_label`" x-model="s.label"
                                       class="glass-input text-sm w-full" placeholder="Logo name (e.g. Main Logo, Partner Logo)">
                                <input type="file" :name="`logo_${i}_file`" accept="image/*"
                                       class="glass-input text-xs py-2 w-full"
                                       @change="previewFile($event, s)">
                            </div>

                            {{-- Remove --}}
                            <button type="button" @click="remove(i)"
                                    class="text-lg leading-none flex-shrink-0 mt-0.5 transition-colors"
                                    style="color:rgba(239,68,68,0.45);"
                                    onmouseover="this.style.color='rgba(239,68,68,0.9)'"
                                    onmouseout="this.style.color='rgba(239,68,68,0.45)'"
                                    title="Remove this logo">×</button>
                        </div>
                    </template>
                </div>

                <p x-show="slots.length === 0" class="text-sm mt-2" style="color:rgba(255,255,255,0.25);">
                    No logos yet. Click "+ Add Logo" to upload one.
                </p>
                <p class="text-xs mt-2" style="color:rgba(255,255,255,0.25);">PNG or JPG, max 2 MB each. After saving, drag logos into position in the <a href="{{ route('tenant.settings.template-builder', $currentTenant->slug) }}" class="underline" style="color:#818cf8;">PDF Builder</a>.</p>
            </div>

            {{-- Signature --}}
            <div>
                <label class="form-label">Doctor / Lab Signature</label>
                @if($tenant->getSetting('report_signature'))
                <div class="mb-3 p-3 rounded-xl" style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.09);">
                    <img src="{{ asset('storage/' . $tenant->getSetting('report_signature')) }}" alt="Signature" class="h-12 object-contain">
                </div>
                @endif
                <input type="file" name="report_signature" accept="image/*" class="glass-input text-sm py-2.5">
                <p class="text-white/25 text-xs mt-1">PNG with transparent background preferred</p>
            </div>

            <div>
                <label class="form-label">Report Header HTML</label>
                <textarea name="report_header_html" class="glass-input" rows="3"
                          placeholder="<h2>City Medical Laboratory</h2><p>123 Health Street | +1-555-0100</p>">{{ old('report_header_html', $tenant->getSetting('report_header_html')) }}</textarea>
                <p class="text-white/25 text-xs mt-1">HTML supported. Appears at the top of every PDF report.</p>
            </div>

            <div>
                <label class="form-label">Report Footer HTML</label>
                <textarea name="report_footer_html" class="glass-input" rows="2"
                          placeholder="<p>This report is confidential. Results valid for 30 days.</p>">{{ old('report_footer_html', $tenant->getSetting('report_footer_html')) }}</textarea>
                <p class="text-white/25 text-xs mt-1">HTML supported. Appears at the bottom of every PDF.</p>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                <button type="submit" class="btn-primary">Save Branding</button>
            </div>
        </form>
    </div>

    {{-- Account Password Tab (Lab Admin only) --}}
    @can('manage-settings')
    <div x-show="tab === 'account'" x-transition class="glass-card p-8" style="display:none">
        <div class="mb-6">
            <h3 class="font-semibold" style="color:#1e293b;">Change Your Password</h3>
            <p class="text-sm mt-1" style="color:#64748b;">Update the password for your account ({{ auth()->user()->email }}). You'll use the new password the next time you sign in.</p>
        </div>

        <form method="POST" action="{{ route('tenant.settings.password', $currentTenant->slug) }}" class="space-y-5 max-w-md">
            @csrf

            <div>
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="glass-input"
                       placeholder="••••••••" required autocomplete="current-password">
                @error('current_password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="glass-input"
                       placeholder="Minimum 8 characters" required autocomplete="new-password">
                @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="glass-input"
                       placeholder="Repeat the new password" required autocomplete="new-password">
            </div>

            <div class="flex items-center gap-4 pt-4" style="border-top: 1px solid rgba(0,0,0,0.08);">
                <button type="submit" class="btn-primary">Change Password</button>
            </div>
        </form>
    </div>
    @endcan
</div>
@endsection

@push('scripts')
<script>
function logoManager(existingLogos) {
    return {
        slots: (existingLogos || []).map((lg, i) => ({
            tempId:   Date.now() + i,
            key:      lg.key || '',
            label:    lg.label || '',
            existing: lg.url || null,
            preview:  null,
        })),
        add() {
            if (this.slots.length >= 5) return;
            this.slots.push({ tempId: Date.now(), key: '', label: '', existing: null, preview: null });
        },
        remove(i) {
            this.slots.splice(i, 1);
        },
        previewFile(event, slot) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => { slot.preview = e.target.result; };
            reader.readAsDataURL(file);
        },
    };
}
</script>
@endpush
