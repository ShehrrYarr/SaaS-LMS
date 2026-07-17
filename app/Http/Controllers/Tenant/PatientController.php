<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\SendPatientCredentialsJob;
use App\Models\Patient;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function __construct(private TenantContext $context) {}

    public function index(Request $request, string $lab_slug)
    {
        $query = Patient::with('branch');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('patient_code', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $patients = $query->latest()->paginate(20)->withQueryString();

        return view('tenant.patients.index', compact('patients'));
    }

    public function create(string $lab_slug)
    {
        return view('tenant.patients.create');
    }

    public function store(Request $request, string $lab_slug)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:191',
            'email'       => 'required|email|max:191',
            'phone'       => 'nullable|string|max:30',
            'dob'         => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:5',
            'address'     => 'nullable|string|max:500',
        ]);

        $tenant = $this->context->get();

        $plainPassword        = Str::random(10);
        $data['password']     = $plainPassword;
        $data['patient_code'] = Patient::generateCode($tenant->id);
        $data['is_active']    = true;

        $patient = Patient::create($data);

        if ($request->boolean('send_credentials') && $patient->email) {
            SendPatientCredentialsJob::dispatch($patient, $tenant, $plainPassword);
        }

        return redirect()->route('tenant.patients.show', [$lab_slug, $patient])
                         ->with('success', "Patient {$patient->name} registered. Code: {$patient->patient_code}");
    }

    public function show(string $lab_slug, Patient $patient)
    {
        $patient->load([
            'branch',
            'appointments' => fn($q) => $q->latest()->limit(5),
            'testOrders'   => fn($q) => $q->latest()->limit(5),
            'invoices'     => fn($q) => $q->latest()->limit(5),
        ]);
        return view('tenant.patients.show', compact('patient'));
    }

    public function edit(string $lab_slug, Patient $patient)
    {
        return view('tenant.patients.edit', compact('patient'));
    }

    public function update(Request $request, string $lab_slug, Patient $patient)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:191',
            'email'       => 'required|email|max:191',
            'phone'       => 'nullable|string|max:30',
            'dob'         => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:5',
            'address'     => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $patient->update($data);

        return redirect()->route('tenant.patients.show', [$lab_slug, $patient])
                         ->with('success', 'Patient updated.');
    }

    public function resetPassword(Request $request, string $lab_slug, Patient $patient)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $patient->update(['password' => $request->password]);

        if ($request->boolean('notify') && $patient->email) {
            $tenant = $this->context->get();
            SendPatientCredentialsJob::dispatch($patient, $tenant, $request->password);
        }

        return back()->with('success', 'Patient password updated successfully.');
    }

    public function destroy(string $lab_slug, Patient $patient)
    {
        $name = $patient->name;
        $patient->delete();
        return redirect()->route('tenant.patients.index', $lab_slug)
                         ->with('success', "{$name} removed.");
    }

}
