@extends('layouts.superadmin')

@section('title', 'Plans')
@section('page-title', 'Plans & Pricing')
@section('page-subtitle', 'Manage subscription plans for laboratories')

@section('topbar-actions')
<a href="{{ route('superadmin.plans.create') }}" class="btn-primary text-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Create Plan
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($plans as $plan)
    <div class="glass-card-hover p-6 flex flex-col gap-4">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-white font-bold text-lg">{{ $plan->name }}</h3>
                <p class="text-white/40 text-sm mt-0.5">{{ $plan->tenants_count }} {{ Str::plural('lab', $plan->tenants_count) }} on this plan</p>
            </div>
            <span class="badge badge-{{ $plan->status === 'active' ? 'success' : 'gray' }}">{{ ucfirst($plan->status) }}</span>
        </div>

        <div class="space-y-2.5 py-4 border-y border-white/8">
            @php
            $features = [
                ['Max Staff', $plan->max_staff >= 9999 ? 'Unlimited' : $plan->max_staff, true],
                ['Max Patients', $plan->max_patients >= 9999 ? 'Unlimited' : $plan->max_patients, true],
                ['Max Branches', $plan->max_branches < 1 ? 'No' : ($plan->max_branches >= 9999 ? 'Unlimited' : $plan->max_branches), $plan->max_branches > 0],
                ['PDF Branding', $plan->pdf_branding ? 'Yes' : 'No', $plan->pdf_branding],
                ['Custom SMTP', $plan->custom_smtp ? 'Yes' : 'No', $plan->custom_smtp],
                ['Analytics', $plan->analytics ? 'Yes' : 'No', $plan->analytics],
            ];
            @endphp
            @foreach($features as [$label, $value, $active])
            <div class="flex items-center justify-between">
                <span class="text-white/50 text-sm">{{ $label }}</span>
                <span class="text-sm font-medium {{ $active ? 'text-emerald-400' : 'text-red-400/70' }}">{{ $value }}</span>
            </div>
            @endforeach
        </div>

        <div class="flex items-center gap-2 mt-auto">
            <a href="{{ route('superadmin.plans.edit', $plan) }}" class="btn-secondary flex-1 justify-center text-sm">Edit</a>
            @if($plan->tenants_count === 0)
            <form method="POST" action="{{ route('superadmin.plans.destroy', $plan) }}"
                  onsubmit="return confirm('Delete this plan?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger text-sm px-4">Delete</button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-20">
        <p class="text-white/30">No plans yet. <a href="{{ route('superadmin.plans.create') }}" class="text-indigo-400 hover:underline">Create one</a></p>
    </div>
    @endforelse
</div>
@endsection
