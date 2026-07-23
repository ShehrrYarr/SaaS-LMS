<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111827; background: #fff; }

    .copy { padding: 18px 28px; }

    .copy-label-bar { display: flex; justify-content: flex-end; margin-bottom: 10px; }
    .copy-label {
        display: inline-block; padding: 3px 10px; border-radius: 4px;
        font-size: 9px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase;
    }
    .copy-label-customer { background: #ede9fe; color: #5b21b6; }
    .copy-label-lab      { background: #e0f2fe; color: #0369a1; }

    .header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; }
    .header-logo img { max-height: 44px; max-width: 140px; object-fit: contain; }
    .invoice-title { font-size: 22px; font-weight: 900; color: #4f46e5; letter-spacing: -0.5px; }
    .invoice-meta  { font-size: 10px; color: #6b7280; margin-top: 3px; }

    .bill-section { display: flex; justify-content: space-between; margin-bottom: 12px; }
    .bill-block   { width: 48%; }
    .bill-label   { font-size: 9px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .bill-name    { font-size: 12px; font-weight: 700; color: #111827; }
    .bill-info    { font-size: 10px; color: #6b7280; margin-top: 2px; }

    .status-badge   { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
    .status-paid    { background: #dcfce7; color: #166534; }
    .status-unpaid  { background: #fee2e2; color: #991b1b; }
    .status-partial { background: #fef3c7; color: #92400e; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    thead tr { background: #4f46e5; }
    thead th { color: #fff; text-align: left; padding: 7px 10px; font-size: 10px; font-weight: 700; }
    tbody tr:nth-child(even) { background: #f9fafb; }
    tbody td { padding: 7px 10px; font-size: 11px; border-bottom: 1px solid #f3f4f6; }

    .totals     { display: flex; flex-direction: column; align-items: flex-end; gap: 3px; margin-bottom: 10px; }
    .total-row  { display: flex; gap: 40px; font-size: 11px; }
    .total-label { color: #6b7280; min-width: 90px; text-align: right; }
    .total-value { min-width: 70px; text-align: right; color: #111827; }
    .grand-total { border-top: 2px solid #4f46e5; padding-top: 5px; margin-top: 3px; }
    .grand-total .total-label,
    .grand-total .total-value { font-size: 13px; font-weight: 900; color: #4f46e5; }

    .payment-info { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 8px 12px; margin-bottom: 8px; }
    .footer-text  { border-top: 1px solid #e5e7eb; padding-top: 8px; font-size: 9px; color: #9ca3af; text-align: center; }

    .cut-divider { display: flex; align-items: center; gap: 8px; padding: 4px 28px; color: #9ca3af; font-size: 9px; }
    .cut-line    { flex: 1; border-top: 1.5px dashed #d1d5db; }
</style>
</head>
<body>

@foreach(['customer' => 'Customer Copy', 'lab' => 'Lab Copy'] as $copyKey => $copyLabel)

@if(!$loop->first)
<div class="cut-divider">
    <div class="cut-line"></div>
    <span>&#9986;&nbsp;&nbsp;cut here</span>
    <div class="cut-line"></div>
</div>
@endif

<div class="copy">

    {{-- Copy label --}}
    <div class="copy-label-bar">
        <span class="copy-label copy-label-{{ $copyKey }}">{{ $copyLabel }}</span>
    </div>

    {{-- Header --}}
    @if(!empty($branding['invoice_template']))
        @include('pdfs.partials.template-section', [
            'tpl'      => $branding['invoice_template'],
            'section'  => 'header',
            'logoFile' => $branding['logo_file'],
            'sigFile'  => $branding['sig_file'],
        ])
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin:10px 0 12px;">
            <div>
                <p class="bill-label">Bill To</p>
                <p class="bill-name">{{ $invoice->patient->name }}</p>
                @if($invoice->patient->email)<p class="bill-info">{{ $invoice->patient->email }}</p>@endif
                @if($invoice->patient->phone)<p class="bill-info">{{ $invoice->patient->phone }}</p>@endif
            </div>
            <div style="text-align:right;">
                <p class="invoice-title">INVOICE</p>
                <p class="invoice-meta">{{ $invoice->invoice_number }}</p>
                <p class="invoice-meta">Issued: {{ $invoice->created_at->format('d M Y') }}</p>
                <div style="margin-top:5px;"><span class="status-badge status-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></div>
            </div>
        </div>
    @else
    <div class="header">
        <div class="header-logo">
            @if($branding['logo_file'])
                <img src="{{ $branding['logo_file'] }}" alt="{{ $tenant->name }}">
            @else
                <div>
                    <p style="font-size:16px;font-weight:900;color:#4f46e5;">{{ $tenant->name }}</p>
                    @if($tenant->email)<p style="font-size:10px;color:#6b7280;margin-top:2px;">{{ $tenant->email }}</p>@endif
                    @if($tenant->phone)<p style="font-size:10px;color:#6b7280;">{{ $tenant->phone }}</p>@endif
                </div>
            @endif
        </div>
        <div style="text-align:right;">
            <p class="invoice-title">INVOICE</p>
            <p class="invoice-meta">{{ $invoice->invoice_number }}</p>
            <p class="invoice-meta">Issued: {{ $invoice->created_at->format('d M Y') }}</p>
            @if($invoice->paid_at)<p class="invoice-meta">Paid: {{ $invoice->paid_at->format('d M Y') }}</p>@endif
            <div style="margin-top:5px;"><span class="status-badge status-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></div>
        </div>
    </div>

    <div class="bill-section">
        <div class="bill-block">
            <p class="bill-label">Bill To</p>
            <p class="bill-name">{{ $invoice->patient->name }}</p>
            @if($invoice->patient->email)<p class="bill-info">{{ $invoice->patient->email }}</p>@endif
            @if($invoice->patient->phone)<p class="bill-info">{{ $invoice->patient->phone }}</p>@endif
            @if($invoice->patient->address)<p class="bill-info" style="margin-top:3px;">{{ $invoice->patient->address }}</p>@endif
        </div>
        <div class="bill-block" style="text-align:right;">
            <p class="bill-label">From</p>
            @if($branding['header_html'])
                <div style="font-size:11px;color:#374151;">{!! $branding['header_html'] !!}</div>
            @else
                <p class="bill-name">{{ $tenant->name }}</p>
                @if($tenant->email)<p class="bill-info">{{ $tenant->email }}</p>@endif
            @endif
        </div>
    </div>
    @endif

    {{-- Items table --}}
    <table>
        <thead><tr>
            <th style="width:50%">Description</th>
            <th style="width:15%;text-align:center;">Qty</th>
            <th style="width:17.5%;text-align:right;">Unit Price</th>
            <th style="width:17.5%;text-align:right;">Amount</th>
        </tr></thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td style="text-align:center;">{{ $item->quantity }}</td>
                <td style="text-align:right;">{{ money($item->unit_price) }}</td>
                <td style="text-align:right;font-weight:700;">{{ money($item->total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <div class="total-row">
            <span class="total-label">Subtotal</span>
            <span class="total-value">{{ money($invoice->subtotal) }}</span>
        </div>
        @if($invoice->discount > 0)
        <div class="total-row">
            <span class="total-label">Discount</span>
            <span class="total-value" style="color:#dc2626;">-{{ money($invoice->discount) }}</span>
        </div>
        @endif
        <div class="total-row grand-total">
            <span class="total-label">Total</span>
            <span class="total-value">{{ money($invoice->total) }}</span>
        </div>
        @if($invoice->amount_paid > 0)
        <div class="total-row" style="margin-top:4px;">
            <span class="total-label" style="color:#16a34a;">Amount Paid</span>
            <span class="total-value" style="color:#16a34a;">{{ money($invoice->amount_paid) }}</span>
        </div>
        @if($invoice->balance > 0)
        <div class="total-row">
            <span class="total-label" style="color:#d97706;">Balance Due</span>
            <span class="total-value" style="color:#d97706;font-weight:700;">{{ money($invoice->balance) }}</span>
        </div>
        @endif
        @endif
    </div>

    @if($invoice->status === 'paid')
    <div class="payment-info">
        <p style="font-size:11px;font-weight:700;color:#166534;">&#10003; PAYMENT RECEIVED IN FULL</p>
        @if($invoice->paid_at)<p style="font-size:10px;color:#16a34a;margin-top:2px;">Payment received on {{ $invoice->paid_at->format('d M Y, H:i') }}</p>@endif
    </div>
    @endif

    @if($invoice->notes)
    <div style="margin-bottom:8px;font-size:10px;color:#6b7280;"><strong>Notes:</strong> {{ $invoice->notes }}</div>
    @endif

    {{-- Footer --}}
    @if(!empty($branding['invoice_template']))
        @include('pdfs.partials.template-section', [
            'tpl'      => $branding['invoice_template'],
            'section'  => 'footer',
            'logoFile' => $branding['logo_file'],
            'sigFile'  => $branding['sig_file'],
        ])
    @else
    <div class="footer-text">
        @if($branding['footer_html'])
            {!! $branding['footer_html'] !!}
        @else
            <p>Thank you for choosing {{ $tenant->name }}. For queries, please contact us.</p>
        @endif
    </div>
    @endif

</div>{{-- .copy --}}
@endforeach

</body>
</html>
