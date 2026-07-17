<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\TestOrder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(string $lab_slug)
    {
        $branchId = Auth::guard('branch')->id();

        $stats = [
            'customers'     => Patient::where('branch_id', $branchId)->count(),
            'orders'        => TestOrder::where('branch_id', $branchId)->count(),
            'pending'       => TestOrder::where('branch_id', $branchId)
                                        ->whereIn('status', ['pending', 'sample_collected', 'processing'])->count(),
            'reports_ready' => TestOrder::where('branch_id', $branchId)
                                        ->whereIn('status', ['results_ready', 'finalized'])->count(),
            'unpaid'        => (float) Invoice::whereHas('patient', fn ($q) => $q->where('branch_id', $branchId))
                                        ->whereIn('status', ['unpaid', 'partial'])
                                        ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as due')
                                        ->value('due'),
        ];

        $recentOrders = TestOrder::with('patient')
            ->where('branch_id', $branchId)
            ->latest()
            ->limit(5)
            ->get();

        return view('branch.dashboard', compact('stats', 'recentOrders'));
    }
}
