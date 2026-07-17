<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Patient Portal') — {{ $currentTenant->name ?? 'Lab Manager' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.theme-style')
</head>
<body class="h-full bg-page" data-theme="light" x-data="{ sidebarOpen: false }">

<div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
    <div class="absolute -top-40 -right-40 w-[500px] h-[500px] rounded-full blur-3xl"
         style="background: radial-gradient(circle, rgba(99,102,241,0.12), transparent);"></div>
    <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full blur-3xl"
         style="background: radial-gradient(circle, rgba(139,92,246,0.10), transparent);"></div>
</div>

<div class="relative flex h-screen overflow-hidden">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 z-20 backdrop-blur-sm lg:hidden"
         style="background: rgba(15,23,42,0.25);"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    {{-- Sidebar --}}
    <aside class="app-sidebar fixed lg:static inset-y-0 left-0 z-30 w-64 flex flex-col transition-transform duration-300 ease-in-out"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           style="background: rgba(255,255,255,0.88); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border-right: 1px solid rgba(0,0,0,0.07); box-shadow: 4px 0 24px rgba(0,0,0,0.05);">

        {{-- Lab branding --}}
        <div class="p-5" style="border-bottom: 1px solid rgba(0,0,0,0.06);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm flex-shrink-0 font-bold"
                     style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff;">
                    {{ strtoupper(substr($currentTenant->name ?? 'L', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-sm truncate" style="color: #1e293b;">{{ $currentTenant->name ?? 'Lab' }}</p>
                    <p class="text-xs" style="color: #94a3b8;">Patient Portal</p>
                </div>
            </div>
        </div>

        {{-- Patient info --}}
        @auth('patient')
        <div class="p-4" style="border-bottom: 1px solid rgba(0,0,0,0.06);">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                     style="background: rgba(99,102,241,0.1);">
                    <span class="text-xs font-bold" style="color: #6366f1;">{{ strtoupper(substr(auth('patient')->user()->name, 0, 2)) }}</span>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium truncate" style="color: #1e293b;">{{ auth('patient')->user()->name }}</p>
                    <p class="text-xs" style="color: #94a3b8;">{{ auth('patient')->user()->patient_code }}</p>
                </div>
            </div>
        </div>
        @endauth

        {{-- Nav --}}
        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
            @php $s = $currentTenant->slug; @endphp
            <a href="{{ route('patient.dashboard', $s) }}"
               class="nav-link {{ request()->routeIs('patient.dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('patient.reports.index', $s) }}"
               class="nav-link {{ request()->routeIs('patient.reports.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                My Reports
            </a>
            <a href="{{ route('patient.invoices.index', $s) }}"
               class="nav-link {{ request()->routeIs('patient.invoices.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Invoices
            </a>
        </nav>

        {{-- Logout --}}
        @auth('patient')
        <div class="p-3" style="border-top: 1px solid rgba(0,0,0,0.06);">
            <form method="POST" action="{{ route('patient.logout', $currentTenant->slug) }}">
                @csrf
                <button type="submit" class="w-full text-left nav-link" style="color: #ef4444;"
                        onmouseover="this.style.background='rgba(239,68,68,0.07)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
        @endauth
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        {{-- Top bar --}}
        <header class="app-topbar flex items-center gap-4 px-5 py-4 flex-shrink-0"
                style="background: rgba(255,255,255,0.75); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid rgba(0,0,0,0.06); box-shadow: 0 1px 8px rgba(0,0,0,0.04);">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden" style="color: #64748b;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div>
                <h1 class="font-semibold text-base" style="color: #1e293b;">@yield('page-title', 'Portal')</h1>
                @hasSection('page-subtitle')<p class="text-xs" style="color: #94a3b8;">@yield('page-subtitle')</p>@endif
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="px-5 pt-4">
            @foreach(['success' => ['bg'=>'16,185,129','color'=>'#059669'], 'error' => ['bg'=>'239,68,68','color'=>'#dc2626'], 'warning' => ['bg'=>'245,158,11','color'=>'#d97706'], 'info' => ['bg'=>'99,102,241','color'=>'#4f46e5']] as $type => $cfg)
            @if(session($type))
            <div class="mb-3 px-4 py-3 rounded-xl text-sm font-medium"
                 style="background:rgba({{ $cfg['bg'] }},0.08);border:1px solid rgba({{ $cfg['bg'] }},0.25);color:{{ $cfg['color'] }}">
                {{ session($type) }}
            </div>
            @endif
            @endforeach
        </div>

        <main class="flex-1 overflow-y-auto px-5 py-4">
            <div class="max-w-5xl mx-auto fade-in">
                @yield('content')
            </div>
        </main>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
