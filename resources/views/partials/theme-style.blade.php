{{--
    Per-lab theme. Emits overriding CSS only when the current tenant saved a
    custom theme in Settings → Appearance. Included by the tenant, patient,
    branch, and auth layouts; superadmin pages have no $currentTenant so this
    stays inert there.
--}}
@if(isset($currentTenant) && $currentTenant && ($themeJson = $currentTenant->getSetting('theme')))
@php
    $t = array_merge(
        \App\Http\Controllers\Tenant\SettingsController::THEME_DEFAULTS,
        json_decode($themeJson, true) ?: []
    );

    $paint = fn (string $area) => $t[$area . '_type'] === 'gradient'
        ? "linear-gradient({$t[$area.'_direction']}, {$t[$area.'_color1']}, {$t[$area.'_color2']})"
        : $t[$area . '_color1'];

    // Convert hex to rgba for glass tint
    $rgba = fn (string $hex, float $a = 0.18) => sprintf(
        'rgba(%d,%d,%d,%.2f)',
        hexdec(substr($hex, 1, 2)),
        hexdec(substr($hex, 3, 2)),
        hexdec(substr($hex, 5, 2)),
        $a
    );
    $paintGlass = fn (string $area) => $t[$area.'_type'] === 'gradient'
        ? "linear-gradient({$t[$area.'_direction']}, {$rgba($t[$area.'_color1'])}, {$rgba($t[$area.'_color2'])})"
        : $rgba($t[$area.'_color1']);
@endphp
<style>
    /* Page background */
    body.bg-page { background: {{ $paint('bg') }} !important; }

    /* Sidebar */
    .app-sidebar {
        background: {{ !empty($t['sidebar_glass']) ? $paintGlass('sidebar') : $paint('sidebar') }} !important;
        @if(!empty($t['sidebar_glass']))
        backdrop-filter: blur(12px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(12px) saturate(180%) !important;
        @endif
    }
    .app-sidebar,
    .app-sidebar .nav-link,
    .app-sidebar [style*="color:"] { color: {{ $t['sidebar_text'] }} !important; }
    .app-sidebar .nav-link.active { color: {{ $t['sidebar_text'] }} !important; }

    /* Top bar */
    .app-topbar {
        background: {{ !empty($t['topbar_glass']) ? $paintGlass('topbar') : $paint('topbar') }} !important;
        @if(!empty($t['topbar_glass']))
        backdrop-filter: blur(12px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(12px) saturate(180%) !important;
        @endif
    }
    .app-topbar,
    .app-topbar .page-title,
    .app-topbar .page-subtitle,
    .app-topbar [style*="color:"] { color: {{ $t['topbar_text'] }} !important; }

    /* Content text — headings */
    main h1:not([style*="color"]), main h2:not([style*="color"]), main h3:not([style*="color"]),
    main h4:not([style*="color"]), main h5:not([style*="color"]),
    main [style*="color:#1e293b"], main [style*="color: #1e293b"] {
        color: {{ $t['heading_text'] }} !important;
    }

    /* Content text — body / muted */
    main { color: {{ $t['body_text'] }}; }
    main [style*="color:#64748b"], main [style*="color: #64748b"],
    main [style*="color:#94a3b8"], main [style*="color: #94a3b8"],
    main [style*="color:#475569"], main [style*="color: #475569"] {
        color: {{ $t['body_text'] }} !important;
    }
</style>
@endif
