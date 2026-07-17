<?php

namespace App\Services;

use App\Models\TestOrder;
use App\Models\Invoice;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfGenerator
{
    public static function report(TestOrder $order, bool $withHeader = true, bool $withFooter = true): \Barryvdh\DomPDF\PDF
    {
        $order->load(['patient', 'items.testCatalog', 'items.enteredBy']);
        $tenant   = $order->tenant;
        $branding = self::loadBranding($tenant);

        // Header/footer can be omitted when printing on pre-printed letterhead.
        $branding['show_header'] = $withHeader;
        $branding['show_footer'] = $withFooter; // footer also covers the signature block

        return Pdf::loadView('pdfs.report', compact('order', 'tenant', 'branding'))
                  ->setPaper('a4');
    }

    public static function invoice(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['patient', 'items', 'testOrder']);
        $tenant   = $invoice->tenant;
        $branding = self::loadBranding($tenant);

        return Pdf::loadView('pdfs.invoice', compact('invoice', 'tenant', 'branding'))
                  ->setPaper('a4');
    }

    private static function loadBranding(Tenant $tenant): array
    {
        $logoPath = $tenant->getSetting('report_logo');
        $sigPath  = $tenant->getSetting('report_signature');

        $logoFile = ($logoPath && file_exists(storage_path('app/public/' . $logoPath)))
            ? storage_path('app/public/' . $logoPath) : null;
        $sigFile  = ($sigPath && file_exists(storage_path('app/public/' . $sigPath)))
            ? storage_path('app/public/' . $sigPath) : null;

        $reportTplRaw  = $tenant->getSetting('report_template');
        $invoiceTplRaw = $tenant->getSetting('invoice_template');

        return [
            'logo'             => $logoPath,
            'logo_file'        => $logoFile,
            'signature'        => $sigPath,
            'sig_file'         => $sigFile,
            'header_html'      => $tenant->getSetting('report_header_html', '<h2>' . $tenant->name . '</h2>'),
            'footer_html'      => $tenant->getSetting('report_footer_html', '<p>' . ($tenant->address ?? '') . '</p>'),
            'report_template'  => $reportTplRaw  ? json_decode($reportTplRaw,  true) : null,
            'invoice_template' => $invoiceTplRaw ? json_decode($invoiceTplRaw, true) : null,
        ];
    }
}
