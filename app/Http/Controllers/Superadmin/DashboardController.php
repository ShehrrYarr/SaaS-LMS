<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TestOrder;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_labs'       => Tenant::count(),
            'active_labs'      => Tenant::where('status', 'active')->count(),
            'suspended_labs'   => Tenant::where('status', 'suspended')->count(),
            'total_staff'      => User::withoutGlobalScope('tenant')->count(),
            'total_patients'   => Patient::withoutGlobalScope('tenant')->count(),
            'total_plans'      => Plan::count(),
        ];

        $recentTenants = Tenant::with('plan')->latest()->take(5)->get();

        $planDistribution = Plan::withCount('tenants')->get();

        return view('superadmin.dashboard', compact('stats', 'recentTenants', 'planDistribution'));
    }
}
