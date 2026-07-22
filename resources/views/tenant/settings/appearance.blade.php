@extends('layouts.tenant')

@section('title', 'Appearance')
@section('page-title', 'Appearance')
@section('page-subtitle', 'Brand your panels with your laboratory colors')

@section('topbar-actions')
<a href="{{ route('tenant.settings.index', $currentTenant->slug) }}" class="btn-secondary text-sm">&larr; Back to Settings</a>
@endsection

@section('content')
<div class="max-w-3xl space-y-6">

    <form method="POST" action="{{ route('tenant.settings.appearance.save', $currentTenant->slug) }}" class="space-y-6">
        @csrf

        {{-- Area cards: Sidebar / Top Bar / Background --}}
        @foreach([
            ['key' => 'sidebar', 'title' => 'Sidebar',    'desc' => 'The navigation panel on the left of every page.', 'glass' => true],
            ['key' => 'topbar',  'title' => 'Top Bar',    'desc' => 'The header strip across the top of every page.',  'glass' => true],
            ['key' => 'bg',      'title' => 'Background', 'desc' => 'The page background behind all content. Also used on your login pages.', 'glass' => false],
        ] as $area)
        @php $k = $area['key']; @endphp
        <div class="glass-card p-8" data-area="{{ $k }}">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="font-semibold" style="color:#1e293b;">{{ $area['title'] }}</h3>
                    <p class="text-sm mt-1" style="color:#64748b;">{{ $area['desc'] }}</p>
                </div>
                {{-- Live preview swatch --}}
                <div class="preview-swatch w-24 h-14 rounded-xl flex-shrink-0" style="border: 1px solid rgba(0,0,0,0.1);"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Style</label>
                    <select name="{{ $k }}_type" class="glass-input area-type">
                        <option value="solid" @selected($theme[$k.'_type'] === 'solid')>Solid Color</option>
                        <option value="gradient" @selected($theme[$k.'_type'] === 'gradient')>Gradient</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Color</label>
                    <input type="color" name="{{ $k }}_color1" value="{{ $theme[$k.'_color1'] }}"
                           class="glass-input area-color1" style="height:42px; padding:4px; cursor:pointer;">
                </div>
                <div class="gradient-only">
                    <label class="form-label">Second Color</label>
                    <input type="color" name="{{ $k }}_color2" value="{{ $theme[$k.'_color2'] }}"
                           class="glass-input area-color2" style="height:42px; padding:4px; cursor:pointer;">
                </div>
                <div class="gradient-only">
                    <label class="form-label">Direction</label>
                    <select name="{{ $k }}_direction" class="glass-input area-direction">
                        <option value="to bottom" @selected($theme[$k.'_direction'] === 'to bottom')>Top → Bottom</option>
                        <option value="to right" @selected($theme[$k.'_direction'] === 'to right')>Left → Right</option>
                        <option value="to bottom right" @selected($theme[$k.'_direction'] === 'to bottom right')>Diagonal</option>
                    </select>
                </div>
            </div>

            @if($area['glass'])
            {{-- Glass / transparency toggle (sidebar & topbar only) --}}
            <div class="flex items-center gap-3 mt-4 pt-4" style="border-top: 1px solid rgba(0,0,0,0.06);">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="{{ $k }}_glass" value="1" class="area-glass"
                           style="width:16px; height:16px; accent-color:#6366f1; cursor:pointer;"
                           @if(!empty($theme[$k.'_glass'])) checked @endif>
                    <span class="text-sm font-medium" style="color:#475569;">Frosted glass transparency</span>
                </label>
                <span class="text-xs px-2 py-0.5 rounded-full"
                      style="background:rgba(99,102,241,0.1); color:#6366f1; font-weight:500;">
                    blur effect
                </span>
                <span class="text-xs" style="color:#94a3b8;">The {{ $k === 'sidebar' ? 'sidebar' : 'top bar' }} color becomes a semi-transparent tint — the background shows through.</span>
            </div>
            @endif
        </div>
        @endforeach

        {{-- Font colors --}}
        <div class="glass-card p-8">
            <div class="mb-5">
                <h3 class="font-semibold" style="color:#1e293b;">Font Colors</h3>
                <p class="text-sm mt-1" style="color:#64748b;">Pick text colors that stay readable on the colors you chose above.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach([
                    ['sidebar_text', 'Sidebar Text'],
                    ['topbar_text', 'Top Bar Text'],
                    ['heading_text', 'Headings'],
                    ['body_text', 'Body Text'],
                ] as [$field, $label])
                <div>
                    <label class="form-label">{{ $label }}</label>
                    <input type="color" name="{{ $field }}" value="{{ $theme[$field] }}"
                           class="glass-input" style="height:42px; padding:4px; cursor:pointer;">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Theme Presets --}}
        <div class="glass-card p-8">
            <div class="mb-5">
                <h3 class="font-semibold" style="color:#1e293b;">Theme Presets</h3>
                <p class="text-sm mt-1" style="color:#64748b;">Click a preset to instantly fill all the fields above, then hit <strong>Save Appearance</strong> to apply it.</p>
            </div>
            <div id="preset-cards" class="grid grid-cols-2 sm:grid-cols-4 gap-3"></div>
        </div>

        @if($errors->any())
        <div class="glass-card p-4 border-red-500/30 bg-red-500/10">
            <ul class="list-disc list-inside text-red-400 text-sm space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Save Appearance</button>
            <a href="{{ route('tenant.settings.index', $currentTenant->slug) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>

    <form method="POST" action="{{ route('tenant.settings.appearance.reset', $currentTenant->slug) }}"
          onsubmit="return confirm('Reset all panels to the default look?')">
        @csrf
        <div class="glass-card p-5 flex items-center justify-between gap-4">
            <p class="text-sm" style="color:#64748b;">Want the original design back? This removes your custom colors from all panels.</p>
            <button type="submit" class="btn-secondary text-sm flex-shrink-0">Reset to Default</button>
        </div>
    </form>
</div>

<script>
// ── Live preview + hide gradient controls for solid areas ──────────────────
document.querySelectorAll('[data-area]').forEach(function (card) {
    var type    = card.querySelector('.area-type');
    var c1      = card.querySelector('.area-color1');
    var c2      = card.querySelector('.area-color2');
    var dir     = card.querySelector('.area-direction');
    var swatch  = card.querySelector('.preview-swatch');
    var gradels = card.querySelectorAll('.gradient-only');

    function refresh() {
        var isGrad = type.value === 'gradient';
        gradels.forEach(function(el) { el.style.display = isGrad ? '' : 'none'; });
        swatch.style.background = isGrad
            ? 'linear-gradient(' + dir.value + ', ' + c1.value + ', ' + c2.value + ')'
            : c1.value;
    }

    [type, c1, c2, dir].forEach(function(el) { el.addEventListener('input', refresh); });
    refresh();
});

// ── Theme presets ──────────────────────────────────────────────────────────
var PRESETS = [
    {
        name: 'Classic', desc: 'Clean white, timeless',
        sidebar_type: 'solid',    sidebar_color1: '#ffffff', sidebar_color2: '#eef2ff', sidebar_direction: 'to bottom',       sidebar_glass: false,
        topbar_type:  'solid',    topbar_color1:  '#ffffff', topbar_color2:  '#eef2ff', topbar_direction:  'to right',        topbar_glass:  false,
        bg_type:      'solid',    bg_color1:      '#eef1f7', bg_color2:      '#e0e7ff', bg_direction:      'to bottom right',
        sidebar_text: '#475569',  topbar_text: '#1e293b', heading_text: '#1e293b', body_text: '#64748b',
    },
    {
        name: 'Glassy', desc: 'Frosted blur panels',
        sidebar_type: 'solid',    sidebar_color1: '#6366f1', sidebar_color2: '#6366f1', sidebar_direction: 'to bottom',       sidebar_glass: true,
        topbar_type:  'solid',    topbar_color1:  '#6366f1', topbar_color2:  '#6366f1', topbar_direction:  'to right',        topbar_glass:  true,
        bg_type:      'gradient', bg_color1:      '#667eea', bg_color2:      '#764ba2', bg_direction:      'to bottom right',
        sidebar_text: '#ffffff',  topbar_text: '#ffffff', heading_text: '#1e293b', body_text: '#374151',
    },
    {
        name: 'Ocean', desc: 'Deep blue serenity',
        sidebar_type: 'gradient', sidebar_color1: '#1e3a5f', sidebar_color2: '#2563eb', sidebar_direction: 'to bottom',       sidebar_glass: false,
        topbar_type:  'solid',    topbar_color1:  '#1e40af', topbar_color2:  '#1e40af', topbar_direction:  'to right',        topbar_glass:  false,
        bg_type:      'gradient', bg_color1:      '#dbeafe', bg_color2:      '#eff6ff', bg_direction:      'to bottom right',
        sidebar_text: '#ffffff',  topbar_text: '#ffffff', heading_text: '#1e3a5f', body_text: '#374151',
    },
    {
        name: 'Midnight', desc: 'Dark, sleek, focused',
        sidebar_type: 'solid',    sidebar_color1: '#0f172a', sidebar_color2: '#1e293b', sidebar_direction: 'to bottom',       sidebar_glass: false,
        topbar_type:  'solid',    topbar_color1:  '#1e293b', topbar_color2:  '#1e293b', topbar_direction:  'to right',        topbar_glass:  false,
        bg_type:      'solid',    bg_color1:      '#0f172a', bg_color2:      '#1e293b', bg_direction:      'to bottom',
        sidebar_text: '#e2e8f0',  topbar_text: '#f1f5f9', heading_text: '#f1f5f9', body_text: '#94a3b8',
    },
    {
        name: 'Forest', desc: 'Natural deep green',
        sidebar_type: 'gradient', sidebar_color1: '#064e3b', sidebar_color2: '#065f46', sidebar_direction: 'to bottom',       sidebar_glass: false,
        topbar_type:  'solid',    topbar_color1:  '#065f46', topbar_color2:  '#065f46', topbar_direction:  'to right',        topbar_glass:  false,
        bg_type:      'gradient', bg_color1:      '#ecfdf5', bg_color2:      '#d1fae5', bg_direction:      'to bottom',
        sidebar_text: '#d1fae5',  topbar_text: '#ecfdf5', heading_text: '#064e3b', body_text: '#374151',
    },
    {
        name: 'Sunset', desc: 'Vibrant purple–pink',
        sidebar_type: 'gradient', sidebar_color1: '#7c3aed', sidebar_color2: '#db2777', sidebar_direction: 'to bottom',       sidebar_glass: false,
        topbar_type:  'solid',    topbar_color1:  '#7c3aed', topbar_color2:  '#7c3aed', topbar_direction:  'to right',        topbar_glass:  false,
        bg_type:      'gradient', bg_color1:      '#fdf4ff', bg_color2:      '#fce7f3', bg_direction:      'to bottom right',
        sidebar_text: '#fce7f3',  topbar_text: '#fce7f3', heading_text: '#4c1d95', body_text: '#6b21a8',
    },
    {
        name: 'Rose Gold', desc: 'Warm, elegant luxury',
        sidebar_type: 'gradient', sidebar_color1: '#be185d', sidebar_color2: '#9d174d', sidebar_direction: 'to bottom',       sidebar_glass: false,
        topbar_type:  'solid',    topbar_color1:  '#be185d', topbar_color2:  '#be185d', topbar_direction:  'to right',        topbar_glass:  false,
        bg_type:      'gradient', bg_color1:      '#fff1f2', bg_color2:      '#fce7f3', bg_direction:      'to bottom',
        sidebar_text: '#fff1f2',  topbar_text: '#fff1f2', heading_text: '#881337', body_text: '#9f1239',
    },
    {
        name: 'Slate', desc: 'Professional grey tones',
        sidebar_type: 'gradient', sidebar_color1: '#1e293b', sidebar_color2: '#334155', sidebar_direction: 'to bottom',       sidebar_glass: false,
        topbar_type:  'solid',    topbar_color1:  '#f8fafc', topbar_color2:  '#f8fafc', topbar_direction:  'to right',        topbar_glass:  false,
        bg_type:      'gradient', bg_color1:      '#f8fafc', bg_color2:      '#f1f5f9', bg_direction:      'to bottom',
        sidebar_text: '#f1f5f9',  topbar_text: '#0f172a', heading_text: '#0f172a', body_text: '#475569',
    },
];

function getBg(type, c1, c2, dir) {
    return type === 'gradient' ? 'linear-gradient(' + dir + ',' + c1 + ',' + c2 + ')' : c1;
}

function hexToRgba(hex, a) {
    var r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
    return 'rgba(' + r + ',' + g + ',' + b + ',' + a + ')';
}

var container = document.getElementById('preset-cards');
PRESETS.forEach(function(p) {
    var bgBg      = getBg(p.bg_type,      p.bg_color1,      p.bg_color2,      p.bg_direction);
    var sidebarBg = p.sidebar_glass
        ? (p.sidebar_type === 'gradient'
            ? 'linear-gradient(' + p.sidebar_direction + ',' + hexToRgba(p.sidebar_color1, 0.35) + ',' + hexToRgba(p.sidebar_color2, 0.35) + ')'
            : hexToRgba(p.sidebar_color1, 0.35))
        : getBg(p.sidebar_type, p.sidebar_color1, p.sidebar_color2, p.sidebar_direction);
    var topbarBg  = p.topbar_glass
        ? hexToRgba(p.topbar_color1, 0.35)
        : getBg(p.topbar_type, p.topbar_color1, p.topbar_color2, p.topbar_direction);

    var card = document.createElement('button');
    card.type = 'button';
    card.style.cssText = 'text-align:left; border:2px solid transparent; border-radius:14px; overflow:hidden; cursor:pointer; transition:border-color 0.15s, transform 0.15s; background:rgba(255,255,255,0.6);';
    card.onmouseover = function() { this.style.borderColor = '#6366f1'; this.style.transform = 'translateY(-2px)'; };
    card.onmouseout  = function() { this.style.borderColor = 'transparent'; this.style.transform = ''; };

    card.innerHTML =
        '<div style="width:100%; height:52px; position:relative; overflow:hidden; background:' + bgBg + '">' +
            '<div style="position:absolute; left:0; top:0; width:28%; height:100%; background:' + sidebarBg + ';' + (p.sidebar_glass ? 'backdrop-filter:blur(4px);' : '') + '"></div>' +
            '<div style="position:absolute; left:28%; top:0; width:72%; height:38%; background:' + topbarBg + ';' + (p.topbar_glass ? 'backdrop-filter:blur(4px);' : '') + '"></div>' +
        '</div>' +
        '<div style="padding:8px 10px;">' +
            '<p style="font-size:0.78rem; font-weight:600; color:#1e293b; margin:0;">' + p.name + (p.sidebar_glass ? ' <span style="font-size:0.65rem; background:rgba(99,102,241,0.12); color:#6366f1; padding:1px 5px; border-radius:4px;">Glass</span>' : '') + '</p>' +
            '<p style="font-size:0.7rem; color:#64748b; margin:2px 0 0;">' + p.desc + '</p>' +
        '</div>';

    card.addEventListener('click', function() { applyPreset(p); });
    container.appendChild(card);
});

function applyPreset(p) {
    var form = document.querySelector('form[action*="appearance/save"]');
    if (!form) form = document.querySelector('form[action*="appearance"]');

    function setSelect(name, value) {
        var el = form.querySelector('select[name="' + name + '"]');
        if (el) { el.value = value; el.dispatchEvent(new Event('input', {bubbles: true})); }
    }
    function setColor(name, value) {
        var el = form.querySelector('input[name="' + name + '"]');
        if (el) { el.value = value; el.dispatchEvent(new Event('input', {bubbles: true})); }
    }
    function setCheck(name, value) {
        var el = form.querySelector('input[name="' + name + '"]');
        if (el) { el.checked = !!value; el.dispatchEvent(new Event('change', {bubbles: true})); }
    }

    ['sidebar', 'topbar', 'bg'].forEach(function(area) {
        setSelect(area + '_type',      p[area + '_type']);
        setColor( area + '_color1',    p[area + '_color1']);
        setColor( area + '_color2',    p[area + '_color2']);
        setSelect(area + '_direction', p[area + '_direction']);
    });
    setCheck('sidebar_glass', p.sidebar_glass);
    setCheck('topbar_glass',  p.topbar_glass);
    ['sidebar_text', 'topbar_text', 'heading_text', 'body_text'].forEach(function(f) {
        setColor(f, p[f]);
    });
}
</script>
@endsection
