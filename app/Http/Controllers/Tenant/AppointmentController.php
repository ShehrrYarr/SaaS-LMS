<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request, string $lab_slug)
    {
        $query = Appointment::with('patient', 'staff');

        if ($request->filled('search')) {
            $query->whereHas('patient', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        $appointments = $query->orderByDesc('scheduled_at')->paginate(20)->withQueryString();
        return view('tenant.appointments.index', compact('appointments'));
    }

    public function create(Request $request, string $lab_slug)
    {
        $patient  = $request->filled('patient_id') ? Patient::findOrFail($request->patient_id) : null;
        $patients = Patient::orderBy('name')->get(['id', 'name', 'patient_code']);
        return view('tenant.appointments.create', compact('patients', 'patient'));
    }

    public function store(Request $request, string $lab_slug)
    {
        $data = $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $data['user_id'] = auth()->id();
        $data['status']  = 'scheduled';

        $appointment = Appointment::create($data);

        if ($request->boolean('create_order')) {
            return redirect()->route('tenant.orders.create', [$lab_slug, 'appointment_id' => $appointment->id]);
        }

        return redirect()->route('tenant.appointments.index', $lab_slug)
                         ->with('success', 'Appointment scheduled.');
    }

    public function show(string $lab_slug, Appointment $appointment)
    {
        $appointment->load('patient', 'staff', 'testOrder.items.testCatalog');
        return view('tenant.appointments.show', compact('appointment'));
    }

    public function edit(string $lab_slug, Appointment $appointment)
    {
        $patients = Patient::orderBy('name')->get(['id', 'name', 'patient_code']);
        return view('tenant.appointments.edit', compact('appointment', 'patients'));
    }

    public function update(Request $request, string $lab_slug, Appointment $appointment)
    {
        $data = $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'scheduled_at' => 'required|date',
            'status'       => 'required|in:scheduled,arrived,completed,cancelled',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $appointment->update($data);

        return redirect()->route('tenant.appointments.index', $lab_slug)
                         ->with('success', 'Appointment updated.');
    }

    public function destroy(string $lab_slug, Appointment $appointment)
    {
        $appointment->delete();
        return redirect()->route('tenant.appointments.index', $lab_slug)
                         ->with('success', 'Appointment removed.');
    }
}
