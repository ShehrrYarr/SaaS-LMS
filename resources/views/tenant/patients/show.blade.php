@extends('layouts.tenant')

@section('title', $patient->name)
@section('page-title', $patient->name)
@section('page-subtitle', 'Patient ID: ' . $patient->patient_code)

@section('topbar-actions')
<div class="flex gap-2">
    <a href="{{ route('tenant.appointments.create', [$currentTenant->slug, 'patient_id' => $patient->id]) }}" class="btn-secondary text-sm">Book Appointment</a>
    <a href="{{ route('tenant.patients.edit', [$currentTenant->slug, $patient]) }}" class="btn-primary text-sm">Edit Patient</a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Profile card --}}
    <div class="glass-card p-6 space-y-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-500/20 flex items-center justify-center flex-shrink-0">
                <span class="text-indigo-400 text-xl font-bold">{{ strtoupper(substr($patient->name, 0, 2)) }}</span>
            </div>
            <div>
                <h3 class="text-white font-semibold">{{ $patient->name }}</h3>
                <p class="text-white/40 text-sm">{{ $patient->patient_code }}</p>
            </div>
        </div>

        <div class="border-t border-white/10 pt-4 space-y-3">
            @php
            $fields = [
                'Email'       => $patient->email,
                'Phone'       => $patient->phone ?? '—',
                'Date of Birth' => $patient->dob ? $patient->dob->format('d M Y') . ' (' . $patient->age . ' years)' : '—',
                'Gender'      => ucfirst($patient->gender ?? '—'),
                'Blood Group' => $patient->blood_group ?? '—',
                'Registered'  => $patient->created_at->format('d M Y'),
                'Registered By' => $patient->branch ? $patient->branch->name . ' (Branch)' : 'Main Lab',
            ];
            @endphp
            @foreach($fields as $label => $value)
            <div class="flex justify-between text-sm gap-3">
                <span class="text-white/40 flex-shrink-0">{{ $label }}</span>
                <span class="text-white text-right">{{ $value }}</span>
            </div>
            @endforeach
        </div>

        @if($patient->address)
        <div class="border-t border-white/10 pt-4">
            <p class="text-white/40 text-xs mb-1">Address</p>
            <p class="text-white/70 text-sm">{{ $patient->address }}</p>
        </div>
        @endif

        <div class="border-t border-white/10 pt-4">
            <span class="badge badge-{{ $patient->is_active ? 'success' : 'gray' }}">
                {{ $patient->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        {{-- Reset Password --}}
        <div class="border-t border-white/10 pt-4 space-y-3">
            <p class="text-white/40 text-xs font-medium uppercase tracking-wide">Reset Portal Password</p>
            <form method="POST" action="{{ route('tenant.patients.reset-password', [$currentTenant->slug, $patient]) }}" class="space-y-3">
                @csrf
                <div>
                    <input type="password" name="password" placeholder="New password" class="glass-input text-sm" required minlength="6">
                    @error('password')<p class="form-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <input type="password" name="password_confirmation" placeholder="Confirm password" class="glass-input text-sm" required>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="notify" value="1" id="notify_pw" class="rounded border-white/20 bg-white/10 text-indigo-500 focus:ring-indigo-500/50">
                    <label for="notify_pw" class="text-white/60 text-xs cursor-pointer">Email new credentials to patient</label>
                </div>
                <button type="submit" class="btn-secondary text-sm w-full">Update Password</button>
            </form>
        </div>
    </div>

    {{-- Activity --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Recent Appointments --}}
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-white font-medium">Recent Appointments</h4>
                <a href="{{ route('tenant.appointments.create', [$currentTenant->slug, 'patient_id' => $patient->id]) }}" class="text-indigo-400 text-sm hover:underline">+ Book</a>
            </div>
            @if($patient->appointments->isEmpty())
            <p class="text-white/30 text-sm">No appointments yet.</p>
            @else
            <div class="space-y-2">
                @foreach($patient->appointments as $apt)
                <div class="flex items-center justify-between p-3 rounded-xl bg-white/5">
                    <div>
                        <p class="text-white text-sm">{{ $apt->scheduled_at->format('d M Y, H:i') }}</p>
                        @if($apt->notes)<p class="text-white/40 text-xs">{{ Str::limit($apt->notes, 60) }}</p>@endif
                    </div>
                    <span class="badge badge-{{ $apt->status_color }}">{{ ucfirst($apt->status) }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Recent Test Orders --}}
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-white font-medium">Recent Test Orders</h4>
                <a href="{{ route('tenant.orders.create', [$currentTenant->slug, 'patient_id' => $patient->id]) }}" class="text-indigo-400 text-sm hover:underline">+ Order</a>
            </div>
            @if($patient->testOrders->isEmpty())
            <p class="text-white/30 text-sm">No test orders yet.</p>
            @else
            <div class="space-y-2">
                @foreach($patient->testOrders as $order)
                <div class="flex items-center justify-between p-3 rounded-xl bg-white/5">
                    <div>
                        <a href="{{ route('tenant.orders.show', [$currentTenant->slug, $order]) }}" class="text-white text-sm hover:text-indigo-300">Order #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</a>
                        <p class="text-white/40 text-xs">{{ $order->created_at->format('d M Y') }}</p>
                    </div>
                    <span class="badge badge-{{ match($order->status) { 'finalized' => 'success', 'results_ready' => 'purple', 'processing' => 'info', 'sample_collected' => 'warning', default => 'gray' } }}">
                        {{ ucwords(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Recent Invoices --}}
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-white font-medium">Recent Invoices</h4>
            </div>
            @if($patient->invoices->isEmpty())
            <p class="text-white/30 text-sm">No invoices yet.</p>
            @else
            <div class="space-y-2">
                @foreach($patient->invoices as $inv)
                <div class="flex items-center justify-between p-3 rounded-xl bg-white/5">
                    <div>
                        <a href="{{ route('tenant.billing.show', [$currentTenant->slug, $inv]) }}" class="text-white text-sm hover:text-indigo-300">Invoice #{{ str_pad($inv->id, 6, '0', STR_PAD_LEFT) }}</a>
                        <p class="text-white/40 text-xs">{{ $inv->created_at->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-white font-medium text-sm">{{ money($inv->total) }}</p>
                        <span class="badge badge-{{ $inv->status === 'paid' ? 'success' : 'warning' }} text-xs">{{ ucfirst($inv->status) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
