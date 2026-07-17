<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Patient;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    public function index()
    {
        $demoTenant = $this->demoTenant();
        return view('landing', compact('demoTenant'));
    }

    /**
     * One-click demo login: signs the visitor into the demo laboratory
     * as the requested role and drops them on that panel's dashboard.
     */
    public function demoLogin(Request $request, string $role)
    {
        abort_unless(in_array($role, ['lab', 'staff', 'customer', 'branch']), 404);

        $tenant = $this->demoTenant();

        if (!$tenant) {
            return redirect()->route('landing')->with('error', 'The demo is not available right now.');
        }

        switch ($role) {
            case 'lab':   // Lab admin = the tenant's oldest user (existing convention)
                $account = User::withoutGlobalScope('tenant')
                               ->where('tenant_id', $tenant->id)
                               ->where('is_active', true)
                               ->oldest()->first();
                $guard   = 'web';
                $target  = route('tenant.dashboard', $tenant->slug);
                break;

            case 'staff': // A regular employee — first active non-admin user, admin as fallback
                $admin   = User::withoutGlobalScope('tenant')
                               ->where('tenant_id', $tenant->id)->oldest()->first();
                $account = User::withoutGlobalScope('tenant')
                               ->where('tenant_id', $tenant->id)
                               ->where('is_active', true)
                               ->when($admin, fn ($q) => $q->where('id', '!=', $admin->id))
                               ->oldest()->first() ?? $admin;
                $guard   = 'web';
                $target  = route('tenant.dashboard', $tenant->slug);
                break;

            case 'customer':
                $account = Patient::withoutGlobalScope('tenant')
                                  ->where('tenant_id', $tenant->id)
                                  ->where('is_active', true)
                                  ->oldest()->first();
                $guard   = 'patient';
                $target  = route('patient.dashboard', $tenant->slug);
                break;

            case 'branch':
                $account = Branch::withoutGlobalScope('tenant')
                                 ->where('tenant_id', $tenant->id)
                                 ->where('is_active', true)
                                 ->oldest()->first();
                $guard   = 'branch';
                $target  = route('branch.dashboard', $tenant->slug);
                break;
        }

        if (!$account) {
            return redirect()->route('landing')->with('error', 'This demo role is not set up yet.');
        }

        Auth::guard($guard)->login($account);
        $request->session()->regenerate();

        return redirect($target);
    }

    private function demoTenant(): ?Tenant
    {
        return Tenant::where('is_demo', true)->where('status', 'active')->first();
    }
}
