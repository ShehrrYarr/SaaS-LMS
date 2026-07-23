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

        // Last 6 months as ordered Carbon periods (oldest → newest)
        $periods = collect(range(5, 0))->map(fn($i) => now()->subMonths($i)->startOfMonth());

        // Monthly patient growth — zero-filled so all 6 months always appear
        $rawGrowth = Patient::select(
            DB::raw('YEAR(created_at) as y'),
            DB::raw('MONTH(created_at) as m'),
            DB::raw('COUNT(*) as cnt')
        )
        ->where('created_at', '>=', $periods->first())
        ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
        ->orderBy(DB::raw('YEAR(created_at)'))->orderBy(DB::raw('MONTH(created_at)'))
        ->get()
        ->keyBy(fn($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $patientGrowth = $periods->map(fn($p) => [
            'month' => $p->format('M'),
            'count' => (int) ($rawGrowth->get($p->format('Y-m'))?->cnt ?? 0),
        ]);

        // Monthly revenue — zero-filled
        $rawRevenue = Invoice::where('status', 'paid')
        ->where('paid_at', '>=', $periods->first())
        ->select(
            DB::raw('YEAR(paid_at) as y'),
            DB::raw('MONTH(paid_at) as m'),
            DB::raw('SUM(amount_paid) as total')
        )
        ->groupBy(DB::raw('YEAR(paid_at)'), DB::raw('MONTH(paid_at)'))
        ->orderBy(DB::raw('YEAR(paid_at)'))->orderBy(DB::raw('MONTH(paid_at)'))
        ->get()
        ->keyBy(fn($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $revenueData = $periods->map(fn($p) => [
            'month' => $p->format('M'),
            'total' => (float) ($rawRevenue->get($p->format('Y-m'))?->total ?? 0),
        ]);

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
