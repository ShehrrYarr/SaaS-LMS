<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lab Manager — Laboratory Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-page" data-theme="light">

{{-- Soft background orbs --}}
<div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
    <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full blur-3xl"
         style="background: radial-gradient(circle, rgba(99,102,241,0.15), transparent);"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full blur-3xl"
         style="background: radial-gradient(circle, rgba(139,92,246,0.12), transparent);"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full blur-3xl"
         style="background: radial-gradient(circle, rgba(79,70,229,0.08), transparent);"></div>
</div>

<div class="relative min-h-screen flex flex-col">

    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-4xl fade-in">

            {{-- Brand / hero --}}
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4"
                     style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 8px 28px rgba(99,102,241,0.35);">
                    <svg class="w-8 h-8" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight" style="color: #1e293b;">Lab Manager</h1>
                <p class="text-base mt-3 max-w-xl mx-auto" style="color: #64748b;">
                    Everything a diagnostic laboratory needs — patients, appointments, test orders,
                    results, branded PDF reports, billing, and multi-branch operations.
                </p>
            </div>

            @if(session('error'))
            <div class="mb-6 max-w-lg mx-auto px-4 py-3 rounded-xl text-sm font-medium text-center"
                 style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); color:#dc2626;">
                {{ session('error') }}
            </div>
            @endif

            @if($demoTenant)
            {{-- Demo role cards --}}
            <p class="text-center text-sm font-semibold uppercase tracking-widest mb-5" style="color: #94a3b8;">
                Try the live demo — one click, no sign-up
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach([
                    ['role' => 'lab',      'title' => 'Lab Login',      'desc' => 'Run the laboratory as the owner — staff, catalog, branches, settings.', 'color' => '#6366f1',
                     'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                    ['role' => 'staff',    'title' => 'Staff Login',    'desc' => 'Work as a lab employee — patients, appointments, orders and results.', 'color' => '#8b5cf6',
                     'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    ['role' => 'customer', 'title' => 'Customer Login', 'desc' => 'See the patient portal — your reports and invoices in one place.', 'color' => '#10b981',
                     'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['role' => 'branch',   'title' => 'Branch Login',   'desc' => 'Operate a collection branch — register customers and assign tests.', 'color' => '#f59e0b',
                     'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                ] as $card)
                <a href="{{ route('demo.login', $card['role']) }}"
                   class="glass-card p-6 flex flex-col items-start gap-3 transition-all hover:-translate-y-1"
                   style="text-decoration:none;"
                   onmouseover="this.style.boxShadow='0 12px 32px rgba(99,102,241,0.18)';"
                   onmouseout="this.style.boxShadow='';">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center"
                         style="background: {{ $card['color'] }}1a;">
                        <svg class="w-5 h-5" fill="none" stroke="{{ $card['color'] }}" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base" style="color: #1e293b;">{{ $card['title'] }}</h3>
                        <p class="text-xs mt-1 leading-relaxed" style="color: #94a3b8;">{{ $card['desc'] }}</p>
                    </div>
                    <span class="mt-auto inline-flex items-center gap-1 text-sm font-semibold" style="color: {{ $card['color'] }};">
                        Enter demo
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </span>
                </a>
                @endforeach
            </div>
            <p class="text-center text-xs mt-5" style="color: #cbd5e1;">
                Demo runs in the "{{ $demoTenant->name }}" environment · destructive actions are disabled
            </p>
            @else
            <div class="glass-card p-8 max-w-lg mx-auto text-center">
                <p class="text-sm" style="color: #64748b;">The live demo is currently unavailable. Please check back soon.</p>
            </div>
            @endif

        </div>
    </main>

    <footer class="py-6 text-center">
        <p class="text-xs" style="color: #cbd5e1;">
            &copy; {{ date('Y') }} Lab Manager. All rights reserved.
            · <a href="{{ route('superadmin.login') }}" class="hover:underline" style="color: #cbd5e1;">Admin</a>
        </p>
    </footer>
</div>

</body>
</html>
