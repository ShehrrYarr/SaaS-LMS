<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Lab Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>[x-cloak]{display:none!important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/apexcharts" defer></script>
    @include('partials.theme-style')
</head>
<body class="h-full bg-page" data-theme="light"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === '1',
          isDesktop: window.innerWidth >= 1024,
          toggleCollapse() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed ? '1' : '0');
          }
      }"
      @resize.window="isDesktop = window.innerWidth >= 1024">

{{-- Soft background orbs --}}
<div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
    <div class="absolute -top-40 -right-40 w-[500px] h-[500px] rounded-full blur-3xl"
         style="background: radial-gradient(circle, rgba(99,102,241,0.12), transparent);"></div>
    <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full blur-3xl"
         style="background: radial-gradient(circle, rgba(139,92,246,0.10), transparent);"></div>
</div>

<div class="relative flex h-screen overflow-hidden">

    @unless(View::hasSection('hide-sidebar'))
    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 z-20 backdrop-blur-sm lg:hidden"
         style="background: rgba(15,23,42,0.25); display:none;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    {{-- ── Sidebar ── --}}
    <aside class="app-sidebar fixed inset-y-0 left-0 z-30 flex-shrink-0 flex flex-col overflow-hidden lg:static lg:translate-x-0 transition-all duration-300"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           :style="(sidebarCollapsed && isDesktop) ? 'width:0; min-width:0;' : 'width:16rem;'"
           style="background: rgba(255,255,255,0.88); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border-right: 1px solid rgba(0,0,0,0.07); box-shadow: 4px 0 24px rgba(0,0,0,0.05);">

        {{-- Sidebar Header --}}
        <div class="flex items-center gap-3 px-5 py-5 flex-shrink-0" style="border-bottom: 1px solid rgba(0,0,0,0.06);">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 3px 10px rgba(99,102,241,0.35);">
                <svg class="w-5 h-5" fill="none" stroke="#fff" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-sm truncate" style="color: #1e293b;">@yield('sidebar-title', 'Lab Manager')</p>
                <p class="text-xs truncate" style="color: #94a3b8;">@yield('sidebar-subtitle', 'Management System')</p>
            </div>
            {{-- Desktop collapse button --}}
            <button x-show="isDesktop"
                    @click="toggleCollapse()"
                    style="width:36px; height:36px; padding:9px; box-sizing:border-box; border-radius:8px; flex-shrink:0; border:none; background:rgba(0,0,0,0.04); cursor:pointer; color:#475569;"
                    onmouseover="this.style.background='rgba(99,102,241,0.12)'; this.style.color='#6366f1';"
                    onmouseout="this.style.background='rgba(0,0,0,0.04)'; this.style.color='#475569';"
                    title="Collapse sidebar">
                <svg style="display:block; width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
            </button>
        </div>

        {{-- Nav links --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            @yield('sidebar-nav')
        </nav>

        {{-- Sidebar footer --}}
        <div class="p-3" style="border-top: 1px solid rgba(0,0,0,0.06);">
            @yield('sidebar-user')
        </div>
    </aside>
    @endunless

    {{-- ── Main content ── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <header class="app-topbar flex items-center gap-4 px-6 py-4 flex-shrink-0"
                style="background: rgba(255,255,255,0.75); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid rgba(0,0,0,0.06); box-shadow: 0 1px 8px rgba(0,0,0,0.04);">

            {{-- Mobile menu toggle (hidden in fullscreen views) --}}
            @unless(View::hasSection('hide-sidebar'))
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden transition-colors" style="color: #64748b;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            {{-- Desktop expand button (only visible when sidebar is collapsed) --}}
            <button x-cloak
                    x-show="sidebarCollapsed && isDesktop"
                    @click="toggleCollapse()"
                    style="width:36px; height:36px; padding:9px; box-sizing:border-box; border-radius:8px; flex-shrink:0; border:none; background:rgba(0,0,0,0.04); cursor:pointer; color:#475569;"
                    onmouseover="this.style.background='rgba(99,102,241,0.12)'; this.style.color='#6366f1';"
                    onmouseout="this.style.background='rgba(0,0,0,0.04)'; this.style.color='#475569';"
                    title="Expand sidebar">
                <svg style="display:block; width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
            </button>
            @endunless

            <div class="flex-1 min-w-0">
                <h1 class="page-title text-lg">@yield('page-title', 'Dashboard')</h1>
                @hasSection('page-subtitle')
                <p class="page-subtitle">@yield('page-subtitle')</p>
                @endif
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                @yield('topbar-actions')
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success') || session('error') || session('warning'))
        <div class="px-6 pt-4" x-data="{ show: true }" x-show="show" x-transition>
            @if(session('success'))
            <div class="flex items-center gap-3 p-4 rounded-xl text-sm mb-0"
                 style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25); color: #059669;">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
                <button @click="show = false" class="ml-auto opacity-60 hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            @endif
            @if(session('error'))
            <div class="flex items-center gap-3 p-4 rounded-xl text-sm"
                 style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); color: #dc2626;">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
                <button @click="show = false" class="ml-auto opacity-60 hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            @endif
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto @yield('main-padding', 'p-6')">
            <div class="@yield('main-class', 'max-w-7xl mx-auto') fade-in">
                @yield('content')
            </div>
        </main>
    </div>
</div>


<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('modals')
@stack('scripts')
</body>
</html>
