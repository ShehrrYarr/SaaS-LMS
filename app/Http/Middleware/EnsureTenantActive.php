<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function __construct(private TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->context->get();

        if (!$tenant || !$tenant->isActive()) {
            abort(403, 'This laboratory account is currently suspended. Please contact support.');
        }

        return $next($request);
    }
}
