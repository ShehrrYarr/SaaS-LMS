<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchActive
{
    /**
     * Ejects a live branch session if the branch was deactivated after login.
     * (Login itself already requires is_active via the attempt credentials.)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $branch = Auth::guard('branch')->user();

        if ($branch && !$branch->is_active) {
            Auth::guard('branch')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('branch.login', $request->route('lab_slug'))
                             ->withErrors(['identifier' => 'This branch account has been deactivated.']);
        }

        return $next($request);
    }
}
