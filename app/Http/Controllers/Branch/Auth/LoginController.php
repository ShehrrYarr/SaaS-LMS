<?php

namespace App\Http\Controllers\Branch\Auth;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(private TenantContext $context) {}

    public function showLogin(string $lab_slug)
    {
        if (Auth::guard('branch')->check()) {
            return redirect()->route('branch.dashboard', $lab_slug);
        }
        return view('branch.auth.login');
    }

    public function login(Request $request, string $lab_slug)
    {
        $credentials = $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required',
        ]);

        $tenant     = $this->context->get();
        $identifier = trim($credentials['identifier']);

        // One field accepts either the branch email or its phone number
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::guard('branch')->attempt([
            $field      => $identifier,
            'password'  => $credentials['password'],
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('branch.dashboard', $lab_slug));
        }

        return back()->withErrors(['identifier' => 'These credentials do not match our records.'])
                     ->withInput($request->only('identifier'));
    }

    public function logout(Request $request, string $lab_slug)
    {
        Auth::guard('branch')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('branch.login', $lab_slug);
    }
}
