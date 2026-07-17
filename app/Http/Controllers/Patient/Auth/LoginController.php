<?php

namespace App\Http\Controllers\Patient\Auth;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(private TenantContext $context) {}

    public function showLogin(string $lab_slug)
    {
        if (Auth::guard('patient')->check()) {
            return redirect()->route('patient.dashboard', $lab_slug);
        }
        return view('patient.auth.login');
    }

    public function login(Request $request, string $lab_slug)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $tenant = $this->context->get();

        if (Auth::guard('patient')->attempt([
            'email'     => $credentials['email'],
            'password'  => $credentials['password'],
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('patient.dashboard', $lab_slug));
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput($request->only('email'));
    }

    public function logout(Request $request, string $lab_slug)
    {
        Auth::guard('patient')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('patient.login', $lab_slug);
    }
}
