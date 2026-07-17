<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('superadmin')->user();
        return view('superadmin.settings', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('superadmin')->user();

        $data = $request->validate([
            'name'  => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:superadmins,email,' . $admin->id,
        ]);

        $admin->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        Auth::guard('superadmin')->user()->update([
            'password'             => Hash::make($request->password),
            'recoverable_password' => $request->password,
        ]);

        return back()->with('success', 'Your password has been changed.');
    }
}
