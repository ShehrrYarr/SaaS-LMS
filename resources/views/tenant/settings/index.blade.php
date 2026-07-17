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
            <p class="text-sm mt-1" style="color:#64748b;">Customize how your reports and invoices look. Logo, header, footer, and signature appear on all generated PDFs.</p>
        </div>

        <form method="POST" action="{{ route('tenant.settings.branding', $currentTenant->slug) }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="form-label">Lab Logo</label>
                    @if($tenant->getSetting('report_logo'))
                    <div class="mb-3 p-3 rounded-xl bg-white/5 border border-white/10">
                        <img src="{{ Storage::url($tenant->getSetting('report_logo')) }}" alt="Logo" class="h-16 object-contain">
                    </div>
                    @endif
                    <input type="file" name="report_logo" accept="image/*" class="glass-input text-sm py-2.5">
                    <p class="text-white/25 text-xs mt-1">PNG or JPG, recommended 300×100px</p>
                </div>

                <div>
                    <label class="form-label">Doctor/Lab Signature</label>
                    @if($tenant->getSetting('report_signature'))
                    <div class="mb-3 p-3 rounded-xl bg-white/5 border border-white/10">
                        <img src="{{ Storage::url($tenant->getSetting('report_signature')) }}" alt="Signature" class="h-12 object-contain">
                    </div>
                    @endif
                    <input type="file" name="report_signature" accept="image/*" class="glass-input text-sm py-2.5">
                    <p class="text-white/25 text-xs mt-1">PNG with transparent background preferred</p>
                </div>
            </div>

            <div>
                <label class="form-label">Report Header</label>
                <textarea name="report_header_html" class="glass-input" rows="3"
                          placeholder="<h2>City Medical Laboratory</h2><p>123 Health Street | +1-555-0100</p>">{{ old('report_header_html', $tenant->getSetting('report_header_html')) }}</textarea>
                <p class="text-white/25 text-xs mt-1">HTML supported. This appears at the top of every PDF report.</p>
            </div>

            <div>
                <label class="form-label">Report Footer</label>
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
