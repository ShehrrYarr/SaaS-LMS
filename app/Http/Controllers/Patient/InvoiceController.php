<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\PdfGenerator;

class InvoiceController extends Controller
{
    public function index(string $lab_slug)
    {
        $patient  = auth('patient')->user();
        $invoices = $patient->invoices()->with('items')->latest()->paginate(15);

        return view('patient.invoices', compact('invoices'));
    }

    public function download(string $lab_slug, Invoice $invoice)
    {
        $patient = auth('patient')->user();

        if ((int) $invoice->patient_id !== $patient->id) {
            abort(403);
        }

        $pdf      = PdfGenerator::invoice($invoice);
        $filename = 'invoice-' . $invoice->invoice_number . '.pdf';
        return $pdf->download($filename);
    }
}
