{{--
  Renders a template section (header or footer) for DomPDF.
  Variables expected: $tpl (array), $section ('header'|'footer'), $logoFile, $sigFile
--}}
@php
  $accentColor = $tpl['accentColor'] ?? '#4f46e5';
  $height      = $section === 'header' ? ($tpl['headerHeight'] ?? 160) : ($tpl['footerHeight'] ?? 90);
  $showDivider = $section === 'header' ? ($tpl['showHeaderDivider'] ?? true) : ($tpl['showFooterDivider'] ?? true);
  $elements    = array_values(array_filter($tpl['elements'] ?? [], fn($e) => ($e['section'] ?? '') === $section));
@endphp
<div style="position:relative; width:720px; height:{{ $height }}px; overflow:hidden;">

  @foreach($elements as $el)
    @php
      $ex = $el['x'] ?? 0;
      $ey = $el['y'] ?? 0;
      $ew = $el['w'] ?? 150;
      $eh = $el['h'] ?? 40;
      $baseStyle = "position:absolute; left:{$ex}px; top:{$ey}px; width:{$ew}px; height:{$eh}px; overflow:hidden;";
    @endphp

    @if($el['type'] === 'logo' && $logoFile)
      <img src="{{ $logoFile }}" style="{{ $baseStyle }} object-fit:contain;">

    @elseif($el['type'] === 'signature' && $sigFile)
      <img src="{{ $sigFile }}" style="{{ $baseStyle }} object-fit:contain;">

    @elseif($el['type'] === 'divider')
      <div style="{{ $baseStyle }} background:{{ $el['color'] ?? $accentColor }};"></div>

    @elseif(in_array($el['type'], ['lab_name', 'contact', 'custom_text']))
      @php
        $text = str_replace("\n", "<br>", e($el['text'] ?? ''));
        $fs   = $el['fontSize']   ?? 12;
        $fw   = $el['fontWeight'] ?? 'normal';
        $fc   = $el['color']      ?? '#1e293b';
        $ta   = $el['textAlign']  ?? 'left';
      @endphp
      <div style="{{ $baseStyle }} font-size:{{ $fs }}px; font-weight:{{ $fw }}; color:{{ $fc }}; text-align:{{ $ta }}; line-height:1.45;">{!! $text !!}</div>
    @endif
  @endforeach

  @if($showDivider)
    @if($section === 'header')
      <div style="position:absolute; bottom:0; left:0; width:720px; height:2px; background:{{ $accentColor }};"></div>
    @else
      <div style="position:absolute; top:0; left:0; width:720px; height:2px; background:{{ $accentColor }};"></div>
    @endif
  @endif
</div>
