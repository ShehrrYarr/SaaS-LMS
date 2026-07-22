<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\TestOrder;
use App\Services\PdfGenerator;

class ReportController extends Controller
{
    public function index(string $lab_slug)
    {
        $patient = auth('patient')->user();
        $orders  = $patient->testOrders()
                           ->with(['items.testCatalog', 'invoice'])
                           ->latest()
                           ->paginate(15);

        return view('patient.reports', compact('orders'));
    }

    public function download(string $lab_slug, TestOrder $order)
    {
        $patient = auth('patient')->user();

        if ((int) $order->patient_id !== $patient->id) {
            abort(403);
        }

        if (!in_array($order->status, ['results_ready', 'finalized'])) {
            return back()->with('error', 'This report is not ready yet.');
        }

        $order->loadMissing('invoice');
        if ($order->invoice && $order->invoice->status !== 'paid') {
            return back()->with('error', 'Please pay your invoice before downloading this report.');
        }

        $pdf      = PdfGenerator::report($order);
        $filename = 'report-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '.pdf';
        return $pdf->download($filename);
    }
}
