<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }
    .page { padding: 30px 34px; }

    /* Letterhead */
    .header { display: flex; align-items: flex-start; justify-content: space-between; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 1.5px solid #1a1a1a; }
    .header-logo img { max-height: 60px; max-width: 220px; object-fit: contain; }
    .header-logo h2 { font-size: 24px; color: #1a1a1a; font-weight: 700; letter-spacing: 0.5px; }
    .header-info { text-align: right; font-size: 10px; color: #444; line-height: 1.5; }
    .header-html { font-size: 12px; color: #333; }
    .header-html h2 { font-size: 17px; color: #1a1a1a; margin-bottom: 2px; }

    /* Demographics (borderless, two columns) */
    .demo { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    .demo td { padding: 2px 4px; font-size: 11px; vertical-align: top; }
    .demo .dl { color: #1a1a1a; width: 14%; white-space: nowrap; }
    .demo .dc { width: 10px; color: #1a1a1a; }
    .demo .dv { color: #1a1a1a; font-weight: 700; width: 34%; }
    .demo-rule { border-bottom: 1.5px solid #1a1a1a; margin: 4px 0 4px; }
    .gen-note { font-size: 9.5px; color: #777; font-style: italic; text-align: right; margin-bottom: 6px; }

    /* Category heading (panel) — plain bold, no fill */
    .cat-title { font-size: 15px; font-weight: 700; color: #1a1a1a; margin: 14px 0 4px; }

    /* Results table — minimal lines */
    table.results { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.results thead th { text-align: left; padding: 4px 8px 1px; font-size: 11px; font-weight: 700; color: #1a1a1a; }
    table.results thead th span { border-bottom: 1.5px solid #1a1a1a; padding-bottom: 2px; }
    table.results td { padding: 2.5px 8px; font-size: 11px; vertical-align: top; }
    .col-test  { width: 38%; }
    .col-res   { width: 18%; }
    .col-unit  { width: 16%; }
    .col-range { width: 28%; }
    .test-name { color: #1a1a1a; }
    .result-value { color: #1a1a1a; }
    .cell-muted { color: #1a1a1a; }

    /* Inline section sub-header — bold + underline, no fill */
    .section-row td { padding: 8px 8px 1px; }
    .section-row span { font-weight: 700; font-size: 11px; color: #1a1a1a; border-bottom: 1px solid #1a1a1a; padding-bottom: 1px; }
    .remark-row td { padding: 1px 8px 4px; font-size: 10px; color: #555; font-style: italic; }

    /* Notes / comments */
    .notes-block { margin-top: 16px; font-size: 10.5px; color: #1a1a1a; line-height: 1.6; }
    .notes-block .nlabel { font-weight: 700; text-decoration: underline; }

    /* Signature / footer */
    .sign-area { display: flex; justify-content: flex-end; margin-top: 44px; }
    .sign-block { text-align: center; }
    .sign-block img { max-height: 48px; max-width: 130px; object-fit: contain; display: block; margin: 0 auto 4px; }
    .sign-line { border-top: 1px solid #777; width: 160px; padding-top: 4px; font-size: 10px; color: #444; }
    .footer { margin-top: 22px; border-top: 1px solid #ccc; padding-top: 10px; font-size: 9.5px; color: #888; text-align: center; }
</style>
</head>
<body>
<div class="page">
    {{-- Letterhead (omitted when printing on pre-printed letterhead) --}}
    @if($branding['show_header'] ?? true)
        @if(!empty($branding['report_template']))
            @include('pdfs.partials.template-section', [
                'tpl'      => $branding['report_template'],
                'section'  => 'header',
                'logoFile' => $branding['logo_file'],
                'sigFile'  => $branding['sig_file'],
            ])
            <div style="margin-bottom:12px;"></div>
        @else
        <div class="header">
            <div class="header-logo">
                @if($branding['logo_file'])
                    <img src="{{ $branding['logo_file'] }}" alt="{{ $tenant->name }}">
                @else
                    <h2>{{ $tenant->name }}</h2>
                @endif
            </div>
            <div class="header-info">
                @if($branding['header_html'])
                    <div class="header-html">{!! $branding['header_html'] !!}</div>
                @else
                    @if($tenant->address)<div>{{ $tenant->address }}</div>@endif
                    @if($tenant->phone)<div>Ph: {{ $tenant->phone }}</div>@endif
                    @if($tenant->email)<div>{{ $tenant->email }}</div>@endif
                @endif
            </div>
        </div>
        @endif
    @else
        <div style="margin-bottom:26px;"></div>
    @endif

    {{-- Patient demographics (borderless) --}}
    @php
        $p = $order->patient;
        $ageSex = '';
        if ($p->dob) { $ageSex .= $p->age . ' Years'; }
        if ($p->gender) { $ageSex .= ($ageSex ? ' / ' : '') . ucfirst($p->gender); }
        $ageSex = $ageSex ?: '—';
    @endphp
    <table class="demo">
        <tr>
            <td class="dl">Patient's Name</td><td class="dc">:</td><td class="dv">{{ $p->name }}</td>
            <td class="dl">Age / Sex</td><td class="dc">:</td><td class="dv">{{ $ageSex }}</td>
        </tr>
        <tr>
            <td class="dl">Lab No.</td><td class="dc">:</td><td class="dv">{{ $p->patient_code ?? str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
            <td class="dl">Reg Date</td><td class="dc">:</td><td class="dv">{{ $order->created_at->format('d-m-Y h:i A') }}</td>
        </tr>
        <tr>
            <td class="dl">Consultant</td><td class="dc">:</td><td class="dv">{{ $order->createdBy->name ?? '—' }}</td>
            <td class="dl">Report Date</td><td class="dc">:</td><td class="dv">{{ ($order->results_ready_at ?? now())->format('d-m-Y h:i A') }}</td>
        </tr>
        @if($p->blood_group)
        <tr>
            <td class="dl">Blood Group</td><td class="dc">:</td><td class="dv">{{ $p->blood_group }}</td>
            <td class="dl">Report No.</td><td class="dc">:</td><td class="dv">{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
        @endif
    </table>
    <div class="demo-rule"></div>
    <div class="gen-note">Electronically generated report.</div>

    {{-- Results --}}
    @php
        // Group items by the panel they came from (null = standalone tests)
        $groups = $order->items->groupBy(fn($i) => $i->panel_id ? ($i->panel_name . '#' . $i->panel_id) : '__standalone__');
    @endphp

    @foreach($groups as $groupKey => $items)
    @php $first = $items->first(); $isPanel = $first->panel_id !== null; @endphp

    <div class="cat-title">{{ $isPanel ? $first->panel_name : 'Test Results' }}</div>

    <table class="results">
        <thead>
            <tr>
                <th class="col-test"><span>Test Name</span></th>
                <th class="col-res"><span>Result</span></th>
                <th class="col-unit"><span>Unit</span></th>
                <th class="col-range"><span>Reference Range</span></th>
            </tr>
        </thead>
        <tbody>
            @php $lastHeader = '__none__'; @endphp
            @foreach($items as $item)
                @php $isText = ($item->result_type ?? $item->testCatalog->result_type ?? 'numeric') === 'text'; @endphp

                {{-- Section sub-header row --}}
                @if($isPanel && $item->section_header && $item->section_header !== $lastHeader)
                <tr class="section-row"><td colspan="4"><span>{{ $item->section_header }}</span></td></tr>
                @endif
                @php $lastHeader = $item->section_header ?? '__none__'; @endphp

                @if($isText)
                {{-- Descriptive result: value spans unit + range --}}
                <tr>
                    <td class="test-name">{{ $item->testCatalog->name }}</td>
                    <td colspan="3" class="result-value">{{ $item->result_value ?? '—' }}</td>
                </tr>
                @else
                <tr>
                    <td class="test-name">{{ $item->testCatalog->name }}</td>
                    <td class="result-value">{{ $item->result_value ?? '—' }}</td>
                    <td class="cell-muted">{{ $item->testCatalog->unit ?? '' }}</td>
                    <td class="cell-muted">{{ $item->testCatalog->normal_range ?? '—' }}</td>
                </tr>
                @endif

                @if($item->remarks)
                <tr class="remark-row"><td colspan="4">Note: {{ $item->remarks }}</td></tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @endforeach

    {{-- Overall comments --}}
    @if($order->notes)
    <div class="notes-block"><span class="nlabel">Comments:</span> {{ $order->notes }}</div>
    @endif

    {{-- Footer + signature (omitted when printing on pre-printed letterhead) --}}
    @if($branding['show_footer'] ?? true)
        @if(empty($branding['report_template']))
        <div class="sign-area">
            <div class="sign-block">
                @if($branding['sig_file'])
                    <img src="{{ $branding['sig_file'] }}" alt="Signature">
                @endif
                <div class="sign-line">Authorized Signature</div>
            </div>
        </div>
        @endif

        @if(!empty($branding['report_template']))
            @include('pdfs.partials.template-section', [
                'tpl'      => $branding['report_template'],
                'section'  => 'footer',
                'logoFile' => $branding['logo_file'],
                'sigFile'  => $branding['sig_file'],
            ])
        @else
        <div class="footer">
            @if($branding['footer_html'])
                {!! $branding['footer_html'] !!}
            @else
                <p>{{ $tenant->name }} &bull; This is an electronically generated report.</p>
            @endif
        </div>
        @endif
    @endif
</div>
</body>
</html>
