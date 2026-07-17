@extends('layouts.tenant')

@section('title', 'Custom Domain')
@section('page-title', 'Custom Domain')
@section('page-subtitle', 'Point your own web address at your laboratory')

@section('topbar-actions')
<a href="{{ route('tenant.settings.index', $currentTenant->slug) }}" class="btn-secondary text-sm">&larr; Back to Settings</a>
@endsection

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="glass-card p-8">
        <div class="mb-6">
            <h3 class="font-semibold" style="color:#1e293b;">Use Your Own Domain</h3>
            <p class="text-sm mt-1" style="color:#64748b;">
                Own a domain like <strong>yourlab.com</strong>? You can point it at your laboratory's pages so your
                staff and patients only need to remember <em>your</em> web address. No technical setup is required
                on our side — you configure a simple redirect at the company where you bought the domain.
            </p>
        </div>

        {{-- The lab's URLs --}}
        <div class="mb-8">
            <p class="form-label mb-2">Your laboratory's addresses</p>
            <div class="space-y-2">
                @php
                    $labUrls = [
                        ['Staff Login', route('tenant.login', $currentTenant->slug)],
                        ['Patient Portal', route('patient.login', $currentTenant->slug)],
                    ];
                    if (($currentTenant->plan?->max_branches ?? 0) > 0) {
                        $labUrls[] = ['Branch Portal', route('branch.login', $currentTenant->slug)];
                    }
                @endphp
                @foreach($labUrls as [$label, $url])
                <div class="flex items-center gap-3 p-3 rounded-xl" style="background: rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.06);">
                    <span class="text-xs font-medium w-28 flex-shrink-0" style="color:#94a3b8;">{{ $label }}</span>
                    <code class="text-sm flex-1 truncate" style="color:#6366f1;">{{ $url }}</code>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $url }}'); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy', 1500);"
                            class="text-xs font-medium px-3 py-1.5 rounded-lg flex-shrink-0 transition-colors"
                            style="background: rgba(99,102,241,0.1); color:#6366f1; border:none; cursor:pointer;">Copy</button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Steps --}}
        <div class="mb-8">
            <p class="form-label mb-3">How to set it up</p>
            <ol class="space-y-4">
                @foreach([
                    ['Buy a domain', 'Purchase a domain from any registrar — for example Namecheap, GoDaddy, Google Domains, or Cloudflare.'],
                    ['Open your registrar\'s forwarding settings', 'Log in to the registrar\'s dashboard and look for a feature called "Domain Forwarding", "URL Redirect", or "URL Forwarding". Every major registrar has one.'],
                    ['Forward your domain to your staff login', 'Set your domain (e.g. www.yourlab.com) to forward to your Staff Login address above. Choose "Permanent (301)" if asked.'],
                    ['Optional: forward subdomains for each portal', 'Create extra forwards so each audience gets a friendly address — e.g. portal.yourlab.com → Patient Portal, and branch.yourlab.com → Branch Portal.'],
                    ['Wait and test', 'DNS changes can take from a few minutes up to an hour. Then open your domain in a browser — it should land on your login page.'],
                ] as $i => [$title, $desc])
                <li class="flex gap-4">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold"
                          style="background: rgba(99,102,241,0.12); color:#6366f1;">{{ $i + 1 }}</span>
                    <div>
                        <p class="text-sm font-medium" style="color:#1e293b;">{{ $title }}</p>
                        <p class="text-sm mt-0.5" style="color:#64748b;">{{ $desc }}</p>
                    </div>
                </li>
                @endforeach
            </ol>
        </div>

        {{-- Notes --}}
        <div class="space-y-3">
            <div class="p-4 rounded-xl" style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25);">
                <p class="text-sm font-medium" style="color:#d97706;">Choose plain forwarding — not "forwarding with masking"</p>
                <p class="text-xs mt-1" style="color:#92400e;">
                    Some registrars offer "masking" or "stealth" forwarding that hides the destination inside a frame.
                    This breaks logins and downloads — always pick the plain redirect option.
                </p>
            </div>
            <div class="p-4 rounded-xl" style="background: rgba(99,102,241,0.06); border: 1px solid rgba(99,102,241,0.15);">
                <p class="text-xs" style="color:#64748b;">
                    <strong style="color:#475569;">Good to know:</strong> after the redirect, the browser's address bar will show
                    this system's address — that is normal for domain forwarding. Your domain acts as an easy-to-remember
                    front door for your staff and patients.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
