<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inside a demo laboratory, visitors may explore and create data but must not
 * be able to break the demo for the next visitor: no deletions, no password
 * changes, no settings/account edits.
 */
class PreventDemoDestruction
{
    private const BLOCKED_ROUTES = [
        // Lab/account settings
        'tenant.settings.smtp',
        'tenant.settings.branding',
        'tenant.settings.password',
        'tenant.settings.template-builder.save',
        'tenant.settings.banks.store',
        'tenant.settings.banks.update',
        'tenant.settings.banks.destroy',
        'tenant.settings.appearance.save',
        'tenant.settings.appearance.reset',
        // Credentials & accounts that demo logins depend on
        'tenant.staff.update',
        'tenant.roles.store',
        'tenant.roles.update',
        'tenant.patients.reset-password',
        'tenant.branches.update',
        'tenant.branches.reset-password',
    ];

    public function __construct(private TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->context->get();

        if (!$tenant || !$tenant->is_demo) {
            return $next($request);
        }

        $blocked = $request->isMethod('DELETE')
                || in_array($request->route()?->getName(), self::BLOCKED_ROUTES, true);

        if ($blocked) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This action is disabled in the demo laboratory.'], 403);
            }
            return back()->with('error', 'This action is disabled in the demo laboratory.');
        }

        return $next($request);
    }
}
