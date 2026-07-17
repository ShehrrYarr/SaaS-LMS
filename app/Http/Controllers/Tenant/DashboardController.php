<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\TestOrder;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(string $lab_slug)
    {
        $stats = [
            'patients'     => Patient::count(),
            'appointments' => Appointment::whereDate('scheduled_at', today())->count(),
            'orders'       => TestOrder::where('status', '!=', 'finalized')->count(),
            'revenue'      => Invoice::where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount_paid'),
        ];

        // Monthly patient growth (last 6 months)
        $patientGrowth = Patient::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
        ->groupBy('year', 'month')
        ->orderBy('year')->orderBy('month')
        ->get()
        ->map(fn($r) => ['month' => date('M', mktime(0, 0, 0, $r->month, 1)), 'count' => $r->count]);

        // Monthly revenue (last 6 months)
        $revenueData = Invoice::where('status', 'paid')
        ->where('paid_at', '>=', now()->subMonths(5)->startOfMonth())
        ->select(
            DB::raw('MONTH(paid_at) as month'),
            DB::raw('YEAR(paid_at) as year'),
            DB::raw('SUM(amount_paid) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year')->orderBy('month')
        ->get()
        ->map(fn($r) => ['month' => date('M', mktime(0, 0, 0, $r->month, 1)), 'total' => (float) $r->total]);

        // Order status distribution
        $orderStatus = TestOrder::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Recent activity
        $recentOrders = TestOrder::with('patient')
            ->latest()->limit(5)->get();

        $pendingPayments = Invoice::where('status', '!=', 'paid')
            ->with('patient')->latest()->limit(5)->get();

        return view('tenant.dashboard', compact(
            'stats', 'patientGrowth', 'revenueData', 'orderStatus', 'recentOrders', 'pendingPayments'
        ));
    }
}
