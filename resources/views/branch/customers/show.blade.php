@extends('layouts.branch')

@section('title', $patient->name)
@section('page-title', $patient->name)
@section('page-subtitle', 'Customer ID: ' . $patient->patient_code)

@section('topbar-actions')
<div class="flex gap-2">
    <a href="{{ route('branch.customers.edit', [$currentTenant->slug, $patient]) }}" class="btn-secondary text-sm">Edit</a>
    <a href="{{ route('branch.orders.create', [$currentTenant->slug, 'patient_id' => $patient->id]) }}" class="btn-primary text-sm">Assign Tests</a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Customer info --}}
    <div class="glass-card p-6">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0" style="background: rgba(99,102,241,0.1);">
                <span class="text-xl font-bold" style="color:#6366f1;">{{ strtoupper(substr($patient->name, 0, 2)) }}</span>
            </div>
            <div>
                <h3 class="font-semibold" style="color:#1e293b;">{{ $patient->name }}</h3>
                <p class="text-sm" style="color:#94a3b8;">{{ $patient->patient_code }}</p>
            </div>
        </div>

        <div class="border-t border-black/5 pt-4 space-y-3">
            @php
            $fields = [
                'Email'         => $patient->email,
                'Phone'         => $patient->phone ?? '—',
                'Date of Birth' => $patient->dob ? $patient->dob->format('d M Y') . ' (' . $patient->age . ' years)' : '—',
                'Gender'        => ucfirst($patient->gender ?? '—'),
                'Blood Group'   => $patient->blood_group ?? '—',
                'Registered'    => $patient->created_at->format('d M Y'),
            ];
            @endphp
            @foreach($fields as $label => $value)
            <div class="flex justify-between text-sm gap-3">
                <span class="flex-shrink-0" style="color:#94a3b8;">{{ $label }}</span>
                <span class="text-right" style="color:#1e293b;">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent orders + invoices --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="glass-card p-6">
            <h3 class="font-semibold mb-4" style="color:#1e293b;">Recent Test Orders</h3>
            @forelse($patient->testOrders as $order)
            <a href="{{ route('branch.orders.show', [$currentTenant->slug, $order]) }}"
               class="flex items-center justify-between py-3 border-b border-black/5 last:border-0 hover:bg-black/[0.02] px-2 rounded-lg transition-colors">
                <div>
                    <p class="text-sm font-medium" style="color:#1e293b;">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-xs" style="color:#94a3b8;">{{ $order->created_at->format('d M Y') }}</p>
                </div>
                <span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span>
            </a>
            @empty
            <p class="text-sm py-4 text-center" style="color:#94a3b8;">No test orders yet.</p>
            @endforelse
        </div>

        <div class="glass-card p-6">
            <h3 class="font-semibold mb-4" style="color:#1e293b;">Recent Invoices</h3>
            @forelse($patient->invoices as $invoice)
            <a href="{{ route('branch.invoices.show', [$currentTenant->slug, $invoice]) }}"
               class="flex items-center justify-between py-3 border-b border-black/5 last:border-0 hover:bg-black/[0.02] px-2 rounded-lg transition-colors">
                <div>
                    <p class="text-sm font-medium font-mono" style="color:#1e293b;">{{ $invoice->invoice_number }}</p>
                    <p class="text-xs" style="color:#94a3b8;">{{ $invoice->created_at->format('d M Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium" style="color:#1e293b;">{{ money($invoice->total) }}</p>
                    <span class="badge badge-{{ $invoice->status_color }}">{{ ucfirst($invoice->status) }}</span>
                </div>
            </a>
            @empty
            <p class="text-sm py-4 text-center" style="color:#94a3b8;">No invoices yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
