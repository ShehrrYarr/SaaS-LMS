<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(private TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('lab_slug');

        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            abort(404, 'Laboratory not found.');
        }

        $this->context->set($tenant);

        view()->share('currentTenant', $tenant);

        return $next($request);
    }
}
