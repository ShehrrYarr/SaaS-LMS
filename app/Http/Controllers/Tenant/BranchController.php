<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function __construct(private TenantContext $context) {}

    public function index(Request $request, string $lab_slug)
    {
        $query = Branch::withCount(['patients', 'testOrders']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $branches = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('tenant.branches.index', compact('branches'));
    }

    public function create(string $lab_slug)
    {
        return view('tenant.branches.create');
    }

    public function store(Request $request, string $lab_slug)
    {
        $tenantId = $this->context->id();

        $data = $request->validate([
            'name'    => 'required|string|max:191',
            'email'   => ['required', 'email', 'max:191',
                          Rule::unique('branches', 'email')->where('tenant_id', $tenantId)],
            'phone'   => ['required', 'string', 'max:30',
                          Rule::unique('branches', 'phone')->where('tenant_id', $tenantId)],
            'address' => 'nullable|string|max:500',
        ]);

        $plainPassword = Str::random(10);

        $branch = Branch::create([
            'name'                 => $data['name'],
            'email'                => $data['email'],
            'phone'                => $data['phone'],
            'address'              => $data['address'] ?? null,
            'password'             => Hash::make($plainPassword),
            'recoverable_password' => $plainPassword,
            'is_active'            => true,
        ]);

        return redirect()->route('tenant.branches.show', [$lab_slug, $branch])
                         ->with('success', "Branch \"{$branch->name}\" created.")
                         ->with('branch_credentials', [
                             'email'     => $branch->email,
                             'phone'     => $branch->phone,
                             'password'  => $plainPassword,
                             'login_url' => route('branch.login', $lab_slug),
                         ]);
    }

    public function show(string $lab_slug, Branch $branch)
    {
        $branch->loadCount(['patients', 'testOrders']);
        return view('tenant.branches.show', compact('branch'));
    }

    public function edit(string $lab_slug, Branch $branch)
    {
        return view('tenant.branches.edit', compact('branch'));
    }

    public function update(Request $request, string $lab_slug, Branch $branch)
    {
        $tenantId = $this->context->id();

        $data = $request->validate([
            'name'      => 'required|string|max:191',
            'email'     => ['required', 'email', 'max:191',
                            Rule::unique('branches', 'email')->where('tenant_id', $tenantId)->ignore($branch->id)],
            'phone'     => ['required', 'string', 'max:30',
                            Rule::unique('branches', 'phone')->where('tenant_id', $tenantId)->ignore($branch->id)],
            'address'   => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $branch->update($data);

        return redirect()->route('tenant.branches.show', [$lab_slug, $branch])
                         ->with('success', 'Branch updated.');
    }

    public function resetPassword(string $lab_slug, Branch $branch)
    {
        $plain = Str::random(10);

        $branch->update([
            'password'             => Hash::make($plain),
            'recoverable_password' => $plain,
        ]);

        return redirect()->route('tenant.branches.show', [$lab_slug, $branch])
                         ->with('success', 'Branch password has been reset.');
    }

    public function destroy(string $lab_slug, Branch $branch)
    {
        if ($branch->patients()->exists() || $branch->testOrders()->exists()) {
            return back()->with('error', 'This branch has customers or orders and cannot be deleted. Deactivate it instead.');
        }

        $name = $branch->name;
        $branch->delete();

        return redirect()->route('tenant.branches.index', $lab_slug)
                         ->with('success', "Branch \"{$name}\" deleted.");
    }
}
