<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Jobs\SendPatientCredentialsJob;
use App\Models\Patient;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function __construct(private TenantContext $context) {}

    private function branchId(): int
    {
        return Auth::guard('branch')->id();
    }

    /** Branches may only touch patients they registered themselves. */
    private function authorizeCustomer(Patient $patient): void
    {
        abort_unless($patient->branch_id === $this->branchId(), 403);
    }

    public function index(Request $request, string $lab_slug)
    {
        $query = Patient::where('branch_id', $this->branchId());

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('patient_code', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $patients = $query->latest()->paginate(20)->withQueryString();
        return view('branch.customers.index', compact('patients'));
    }

    public function create(string $lab_slug)
    {
        return view('branch.customers.create');
    }

    public function store(Request $request, string $lab_slug)
    {
        $tenant = $this->context->get();

        $data = $request->validate([
            'name'        => 'required|string|max:191',
            'email'       => ['required', 'email', 'max:191',
                              Rule::unique('patients', 'email')->where('tenant_id', $tenant->id)],
            'phone'       => 'nullable|string|max:30',
            'dob'         => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:5',
            'address'     => 'nullable|string|max:500',
        ], [
            'email.unique' => 'A patient with this email is already registered at this laboratory.',
        ]);

        $plainPassword        = Str::random(10);
        $data['password']     = $plainPassword;
        $data['patient_code'] = Patient::generateCode($tenant->id);
        $data['branch_id']    = $this->branchId();
        $data['is_active']    = true;

        $patient = Patient::create($data);

        if ($request->boolean('send_credentials') && $patient->email) {
            SendPatientCredentialsJob::dispatch($patient, $tenant, $plainPassword);
        }

        return redirect()->route('branch.customers.show', [$lab_slug, $patient])
                         ->with('success', "Customer {$patient->name} registered. Code: {$patient->patient_code}");
    }

    public function show(string $lab_slug, Patient $patient)
    {
        $this->authorizeCustomer($patient);

        $patient->load([
            'testOrders' => fn ($q) => $q->latest()->limit(5),
            'invoices'   => fn ($q) => $q->latest()->limit(5),
        ]);

        return view('branch.customers.show', compact('patient'));
    }

    public function edit(string $lab_slug, Patient $patient)
    {
        $this->authorizeCustomer($patient);
        return view('branch.customers.edit', compact('patient'));
    }

    public function update(Request $request, string $lab_slug, Patient $patient)
    {
        $this->authorizeCustomer($patient);

        $tenant = $this->context->get();

        $data = $request->validate([
            'name'        => 'required|string|max:191',
            'email'       => ['required', 'email', 'max:191',
                              Rule::unique('patients', 'email')->where('tenant_id', $tenant->id)->ignore($patient->id)],
            'phone'       => 'nullable|string|max:30',
            'dob'         => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:5',
            'address'     => 'nullable|string|max:500',
        ], [
            'email.unique' => 'A patient with this email is already registered at this laboratory.',
        ]);

        $patient->update($data);

        return redirect()->route('branch.customers.show', [$lab_slug, $patient])
                         ->with('success', 'Customer updated.');
    }
}
