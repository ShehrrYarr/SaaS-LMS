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
            ['key' => 'sidebar', 'title' => 'Sidebar',    'desc' => 'The navigation panel on the left of every page.'],
            ['key' => 'topbar',  'title' => 'Top Bar',    'desc' => 'The header strip across the top of every page.'],
            ['key' => 'bg',      'title' => 'Background', 'desc' => 'The page background behind all content. Also used on your login pages.'],
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
// Live preview + hide gradient controls for solid areas (vanilla JS)
document.querySelectorAll('[data-area]').forEach(function (card) {
    const type    = card.querySelector('.area-type');
    const c1      = card.querySelector('.area-color1');
    const c2      = card.querySelector('.area-color2');
    const dir     = card.querySelector('.area-direction');
    const swatch  = card.querySelector('.preview-swatch');
    const gradels = card.querySelectorAll('.gradient-only');

    function refresh() {
        const isGrad = type.value === 'gradient';
        gradels.forEach(el => el.style.display = isGrad ? '' : 'none');
        swatch.style.background = isGrad
            ? 'linear-gradient(' + dir.value + ', ' + c1.value + ', ' + c2.value + ')'
            : c1.value;
    }

    [type, c1, c2, dir].forEach(el => el.addEventListener('input', refresh));
    refresh();
});
</script>
@endsection
