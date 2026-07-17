<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function __construct(private TenantContext $context) {}

    public function index(string $lab_slug)
    {
        $staff = User::with('roles')->paginate(20);
        return view('tenant.staff.index', compact('staff'));
    }

    public function create(string $lab_slug)
    {
        $roles = Role::where('team_id', $this->context->id())->get();
        return view('tenant.staff.create', compact('roles'));
    }

    public function store(Request $request, string $lab_slug)
    {
        $tenantId = $this->context->id();
        $tenant   = $this->context->get();

        // Plan limit check
        $plan = $tenant->plan;
        if ($plan && User::count() >= $plan->max_staff) {
            return back()->with('error', "Your plan allows a maximum of {$plan->max_staff} staff members.");
        }

        $data = $request->validate([
            'name'     => 'required|string|max:191',
            'email'    => 'required|email|max:191|unique:users,email,NULL,id,tenant_id,' . $tenantId,
            'password' => 'required|min:8|confirmed',
            'phone'    => 'nullable|string|max:30',
            'role_id'  => 'nullable|exists:roles,id',
        ]);

        $user = User::create([
            'tenant_id' => $tenantId,
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'phone'     => $data['phone'] ?? null,
            'is_active' => true,
        ]);

        if (!empty($data['role_id'])) {
            setPermissionsTeamId($tenantId);
            $role = Role::find($data['role_id']);
            if ($role) {
                $user->assignRole($role);
            }
        }

        return redirect()->route('tenant.staff.index', $lab_slug)
                         ->with('success', "Staff member \"{$user->name}\" added.");
    }

    public function edit(string $lab_slug, User $staff)
    {
        $roles = Role::where('team_id', $this->context->id())->get();
        return view('tenant.staff.edit', compact('staff', 'roles'));
    }

    public function update(Request $request, string $lab_slug, User $staff)
    {
        $tenantId = $this->context->id();

        $data = $request->validate([
            'name'     => 'required|string|max:191',
            'email'    => 'required|email|max:191|unique:users,email,' . $staff->id . ',id,tenant_id,' . $tenantId,
            'phone'    => 'nullable|string|max:30',
            'password' => 'nullable|min:8|confirmed',
            'role_id'  => 'nullable|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $staff->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        if (!empty($data['password'])) {
            // The lab admin (tenant's oldest user) keeps a recoverable copy
            // so the superadmin's password view stays accurate.
            $isLabAdmin = $staff->id === User::oldest()->value('id');

            $staff->update(array_merge(
                ['password' => Hash::make($data['password'])],
                $isLabAdmin ? ['recoverable_password' => $data['password']] : []
            ));
        }

        if (isset($data['role_id'])) {
            setPermissionsTeamId($tenantId);
            $role = Role::find($data['role_id']);
            $staff->syncRoles($role ? [$role] : []);
        }

        return redirect()->route('tenant.staff.index', $lab_slug)
                         ->with('success', 'Staff member updated.');
    }

    public function destroy(string $lab_slug, User $staff)
    {
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $staff->delete();
        return redirect()->route('tenant.staff.index', $lab_slug)->with('success', 'Staff member removed.');
    }
}
