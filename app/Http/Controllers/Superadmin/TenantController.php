<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('plan');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tenants = $query->latest()->paginate(15)->withQueryString();
        $plans   = Plan::where('status', 'active')->get();

        return view('superadmin.tenants.index', compact('tenants', 'plans'));
    }

    public function create()
    {
        $plans = Plan::where('status', 'active')->get();
        return view('superadmin.tenants.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:191',
            'slug'    => 'required|string|max:100|unique:tenants,slug|alpha_dash',
            'plan_id' => 'required|exists:plans,id',
            'email'   => 'nullable|email|max:191',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
        ]);

        $data['slug']   = Str::slug($data['slug']);
        $data['status'] = 'active';

        $tenant = Tenant::create($data);

        // Auto-create the initial Lab Admin account
        $plainPassword = Str::random(12);

        $adminUser = User::withoutGlobalScope('tenant')->create([
            'tenant_id'            => $tenant->id,
            'name'                 => $data['name'] . ' Admin',
            'email'                => $data['email'] ?? ('admin@' . $data['slug'] . '.lab'),
            'password'             => Hash::make($plainPassword),
            'recoverable_password' => $plainPassword,
        ]);

        $this->seedLabAdminRole($tenant->id, $adminUser);

        $loginUrl = url("/{$data['slug']}/login");

        return redirect()->route('superadmin.tenants.show', $tenant)
                         ->with('success', "Laboratory \"{$data['name']}\" created.")
                         ->with('lab_credentials', [
                             'email'    => $data['email'] ?? ('admin@' . $data['slug'] . '.lab'),
                             'password' => $plainPassword,
                             'login_url'=> $loginUrl,
                         ]);
    }

    public function resetAdminPassword(Tenant $tenant)
    {
        $admin = User::withoutGlobalScope('tenant')
                     ->where('tenant_id', $tenant->id)
                     ->oldest()
                     ->first();

        if (! $admin) {
            return back()->with('error', 'No staff account found for this laboratory.');
        }

        $plain = Str::random(12);
        $admin->update([
            'password'             => Hash::make($plain),
            'recoverable_password' => $plain,
        ]);

        return redirect()->route('superadmin.tenants.show', $tenant)
                         ->with('reset_credentials', [
                             'email'    => $admin->email,
                             'password' => $plain,
                         ]);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('plan');
        $staffCount   = $tenant->users()->withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->count();
        $patientCount = $tenant->patients()->withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->count();
        $labAdmin     = User::withoutGlobalScope('tenant')
                            ->where('tenant_id', $tenant->id)
                            ->oldest()
                            ->first();

        return view('superadmin.tenants.show', compact('tenant', 'staffCount', 'patientCount', 'labAdmin'));
    }

    public function edit(Tenant $tenant)
    {
        $plans = Plan::where('status', 'active')->get();
        return view('superadmin.tenants.edit', compact('tenant', 'plans'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:191',
            'plan_id' => 'required|exists:plans,id',
            'email'   => 'nullable|email|max:191',
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
        ]);

        $tenant->update($data);

        return redirect()->route('superadmin.tenants.index')
                         ->with('success', "Laboratory updated successfully.");
    }

    public function updateStatus(Request $request, Tenant $tenant)
    {
        $request->validate(['status' => 'required|in:active,suspended,inactive']);
        $tenant->update(['status' => $request->status]);

        $label = ucfirst($request->status);
        return back()->with('success', "Laboratory status changed to {$label}.");
    }

    public function toggleDemo(Tenant $tenant)
    {
        if ($tenant->is_demo) {
            $tenant->update(['is_demo' => false]);
            return back()->with('success', "\"{$tenant->name}\" is no longer the demo laboratory.");
        }

        // Only one demo lab at a time — the landing page features a single one
        Tenant::where('is_demo', true)->update(['is_demo' => false]);
        $tenant->update(['is_demo' => true]);

        return back()->with('success', "\"{$tenant->name}\" is now featured as the demo laboratory on the landing page.");
    }

    public function destroy(Tenant $tenant)
    {
        $name = $tenant->name;
        $tenant->delete();
        return redirect()->route('superadmin.tenants.index')
                         ->with('success', "Laboratory \"{$name}\" deleted.");
    }

    private function seedLabAdminRole(int $tenantId, User $user): void
    {
        // Permissions are global in Spatie (no team_id on permissions table)
        $allPermissions = [
            'manage-staff', 'manage-settings', 'manage-billing',
            'manage-tests', 'manage-patients', 'manage-appointments',
            'manage-orders', 'view-reports',
        ];

        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Roles are team-scoped — set team context before creating/assigning
        setPermissionsTeamId($tenantId);

        $role = Role::firstOrCreate([
            'name'       => 'Lab Admin',
            'guard_name' => 'web',
            'team_id'    => $tenantId,
        ]);

        $role->syncPermissions($allPermissions);
        $user->assignRole($role);
    }
}
