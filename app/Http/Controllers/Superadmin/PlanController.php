<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('tenants')->get();
        return view('superadmin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('superadmin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100|unique:plans,name',
            'max_staff'    => 'required|integer|min:1',
            'max_patients' => 'required|integer|min:1',
            'max_branches' => 'required|integer|min:0',
            'pdf_branding' => 'boolean',
            'custom_smtp'  => 'boolean',
            'analytics'    => 'boolean',
            'status'       => 'required|in:active,inactive',
        ]);

        $data['pdf_branding'] = $request->boolean('pdf_branding');
        $data['custom_smtp']  = $request->boolean('custom_smtp');
        $data['analytics']    = $request->boolean('analytics');

        Plan::create($data);

        return redirect()->route('superadmin.plans.index')
                         ->with('success', "Plan \"{$data['name']}\" created.");
    }

    public function show(Plan $plan)
    {
        $plan->loadCount('tenants');
        return view('superadmin.plans.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        return view('superadmin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100|unique:plans,name,' . $plan->id,
            'max_staff'    => 'required|integer|min:1',
            'max_patients' => 'required|integer|min:1',
            'max_branches' => 'required|integer|min:0',
            'pdf_branding' => 'boolean',
            'custom_smtp'  => 'boolean',
            'analytics'    => 'boolean',
            'status'       => 'required|in:active,inactive',
        ]);

        $data['pdf_branding'] = $request->boolean('pdf_branding');
        $data['custom_smtp']  = $request->boolean('custom_smtp');
        $data['analytics']    = $request->boolean('analytics');

        $plan->update($data);

        return redirect()->route('superadmin.plans.index')
                         ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->tenants()->exists()) {
            return back()->with('error', 'Cannot delete a plan with active laboratories. Reassign labs first.');
        }

        $name = $plan->name;
        $plan->delete();
        return redirect()->route('superadmin.plans.index')
                         ->with('success', "Plan \"{$name}\" deleted.");
    }
}
