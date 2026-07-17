<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(string $lab_slug)
    {
        $patient = auth('patient')->user();

        $patient->load([
            'appointments' => fn($q) => $q->latest()->limit(3),
            'testOrders'   => fn($q) => $q->latest()->limit(5)->with(['items.testCatalog']),
            'invoices'     => fn($q) => $q->latest()->limit(3),
        ]);

        $stats = [
            'total_orders'   => $patient->testOrders()->count(),
            'ready_reports'  => $patient->testOrders()->whereIn('status', ['results_ready', 'finalized'])->count(),
            'unpaid_invoices' => $patient->invoices()->where('status', '!=', 'paid')->count(),
        ];

        return view('patient.dashboard', compact('patient', 'stats'));
    }
}
