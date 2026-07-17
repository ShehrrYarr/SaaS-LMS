<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanLimit
{
    public function __construct(private TenantContext $context) {}

    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $tenant = $this->context->get();
        $plan   = $tenant?->plan;

        if (!$plan) {
            return $next($request);
        }

        match ($resource) {
            'staff' => $this->checkStaff($tenant, $plan),
            'patient' => $this->checkPatients($tenant, $plan),
            'branch' => $this->checkBranches($tenant, $plan),
            'branch-feature' => $this->checkBranchFeature($plan),
            default => null,
        };

        return $next($request);
    }

    private function checkStaff($tenant, $plan): void
    {
        if ($tenant->users()->count() >= $plan->max_staff) {
            abort(403, "Your plan allows a maximum of {$plan->max_staff} staff members. Please upgrade to add more.");
        }
    }

    private function checkPatients($tenant, $plan): void
    {
        if ($tenant->patients()->count() >= $plan->max_patients) {
            abort(403, "Your plan allows a maximum of {$plan->max_patients} patients. Please upgrade to add more.");
        }
    }

    private function checkBranches($tenant, $plan): void
    {
        if ($tenant->branches()->count() >= $plan->max_branches) {
            abort(403, "Your plan allows a maximum of {$plan->max_branches} branches. Please upgrade to add more.");
        }
    }

    private function checkBranchFeature($plan): void
    {
        if ($plan->max_branches < 1) {
            abort(403, 'Branch management is not included in your plan. Please upgrade to the Enterprise package.');
        }
    }
}
