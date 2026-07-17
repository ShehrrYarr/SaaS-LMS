<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // Grouped permissions available in the system
    public const PERMISSION_GROUPS = [
        'Patients'     => ['view-patients', 'create-patients', 'edit-patients', 'delete-patients'],
        'Appointments' => ['view-appointments', 'create-appointments', 'edit-appointments'],
        'Test Orders'  => ['view-orders', 'create-orders', 'edit-orders', 'delete-orders', 'enter-results'],
        'Billing'      => ['view-billing', 'create-invoices', 'mark-paid'],
        'Test Catalog' => ['view-tests', 'manage-tests'],
        'Staff'        => ['manage-staff'],
        'Settings'     => ['manage-settings'],
        'Reports'      => ['download-reports'],
    ];

    public function __construct(private TenantContext $context) {}

    public function index(string $lab_slug)
    {
        $tenantId   = $this->context->id();
        $roles      = Role::where('team_id', $tenantId)->with('permissions')->get();
        $allPerms   = collect(array_merge(...array_values(self::PERMISSION_GROUPS)));

        return view('tenant.roles.index', compact('roles', 'allPerms'));
    }

    public function store(Request $request, string $lab_slug)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $tenantId = $this->context->id();

        setPermissionsTeamId($tenantId);

        $existing = Role::where('name', $request->name)->where('team_id', $tenantId)->first();
        if ($existing) {
            return back()->with('error', 'A role with that name already exists.');
        }

        Role::create(['name' => $request->name, 'guard_name' => 'web', 'team_id' => $tenantId]);

        return back()->with('success', "Role \"{$request->name}\" created.");
    }

    public function update(Request $request, string $lab_slug, Role $role)
    {
        $tenantId = $this->context->id();

        if ($role->team_id !== $tenantId) {
            abort(403);
        }

        setPermissionsTeamId($tenantId);

        $permissions = $request->input('permissions', []);

        $this->ensurePermissionsExist($permissions);

        $role->syncPermissions($permissions);

        return back()->with('success', "Permissions updated for role \"{$role->name}\".");
    }

    public function destroy(string $lab_slug, Role $role)
    {
        $tenantId = $this->context->id();

        if ($role->team_id !== $tenantId) {
            abort(403);
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete a role that is assigned to staff. Unassign it first.');
        }

        $name = $role->name;
        $role->delete();

        return back()->with('success', "Role \"{$name}\" deleted.");
    }

    private function ensurePermissionsExist(array $names): void
    {
        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }
}
