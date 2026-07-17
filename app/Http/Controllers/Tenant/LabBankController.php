<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\LabBank;
use Illuminate\Http\Request;

class LabBankController extends Controller
{
    public function index(string $lab_slug)
    {
        $banks = LabBank::latest()->get();
        return view('tenant.settings.banks', compact('banks'));
    }

    public function store(Request $request, string $lab_slug)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:191',
            'account_title'  => 'nullable|string|max:191',
            'account_number' => 'nullable|string|max:100',
            'branch'         => 'nullable|string|max:191',
        ]);

        LabBank::create($data);

        return back()->with('success', "Bank \"{$data['name']}\" added.");
    }

    public function update(Request $request, string $lab_slug, LabBank $bank)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:191',
            'account_title'  => 'nullable|string|max:191',
            'account_number' => 'nullable|string|max:100',
            'branch'         => 'nullable|string|max:191',
            'is_active'      => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $bank->update($data);

        return back()->with('success', 'Bank updated.');
    }

    public function destroy(string $lab_slug, LabBank $bank)
    {
        if ($bank->payments()->exists()) {
            $bank->update(['is_active' => false]);
            return back()->with('success', 'Bank deactivated (it has payment records and cannot be deleted).');
        }

        $bank->delete();
        return back()->with('success', 'Bank deleted.');
    }
}
