<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lab Manager') | Lab Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme-style')
</head>
<body class="h-full bg-page overflow-hidden" data-theme="light">

{{-- Soft background orbs --}}
<div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
    <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full blur-3xl"
         style="background: radial-gradient(circle, rgba(99,102,241,0.15), transparent);"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full blur-3xl"
         style="background: radial-gradient(circle, rgba(139,92,246,0.12), transparent);"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full blur-3xl"
         style="background: radial-gradient(circle, rgba(79,70,229,0.08), transparent);"></div>
</div>

<div class="relative min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md fade-in">
        {{-- Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4"
                 style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 8px 28px rgba(99,102,241,0.35);">
                <svg class="w-8 h-8" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight" style="color: #1e293b;">Lab Manager</h1>
            <p class="text-sm mt-1" style="color: #94a3b8;">@yield('brand-subtitle', 'Laboratory Management System')</p>
        </div>

        {{-- Card --}}
        <div class="glass-card p-8">
            @yield('content')
        </div>

        <p class="text-center text-xs mt-6" style="color: #cbd5e1;">
            &copy; {{ date('Y') }} Lab Manager. All rights reserved.
        </p>
    </div>
</div>


<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
