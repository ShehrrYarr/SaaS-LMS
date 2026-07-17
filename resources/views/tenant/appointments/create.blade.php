@extends('layouts.tenant')

@section('title', 'Book Appointment')
@section('page-title', 'Book Appointment')
@section('page-subtitle', 'Schedule a new patient appointment')

@section('topbar-actions')
<a href="{{ route('tenant.appointments.index', $currentTenant->slug) }}" class="btn-secondary text-sm">&larr; Back</a>
@endsection

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('tenant.appointments.store', $currentTenant->slug) }}" class="space-y-6">
        @csrf
        <div class="glass-card p-8 space-y-5">
            <div>
                <label class="form-label">Patient <span class="text-red-400">*</span></label>
                <select name="patient_id" class="glass-input" required>
                    <option value="">Select patient…</option>
                    @foreach($patients as $p)
                    <option value="{{ $p->id }}" @selected(old('patient_id', $patient?->id) == $p->id)>
                        {{ $p->name }} ({{ $p->patient_code }})
                    </option>
                    @endforeach
                </select>
                @error('patient_id')<p class="form-error">{{ $message }}</p>@enderror
                <a href="{{ route('tenant.patients.create', $currentTenant->slug) }}" class="text-indigo-400 text-xs hover:underline mt-1 inline-block">+ Register new patient</a>
            </div>

            <div>
                <label class="form-label">Date & Time <span class="text-red-400">*</span></label>
                <input type="datetime-local" name="scheduled_at"
                       value="{{ old('scheduled_at') }}"
                       class="glass-input" required>
                @error('scheduled_at')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" class="glass-input" rows="3"
                          placeholder="Reason for visit, special instructions…">{{ old('notes') }}</textarea>
            </div>

            <div class="border-t border-white/10 pt-5">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="create_order" value="1"
                           class="mt-0.5 rounded border-white/20 bg-white/10 text-indigo-500 focus:ring-indigo-500/50">
                    <div>
                        <p class="text-white text-sm font-medium">Proceed to Test Order</p>
                        <p class="text-white/40 text-xs mt-0.5">After saving, you'll be redirected to create a test order for this appointment.</p>
                    </div>
                </label>
            </div>
        </div>

        @if($errors->any())
        <div class="glass-card p-4 border-red-500/30 bg-red-500/10">
            <ul class="list-disc list-inside text-red-400 text-sm space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Book Appointment</button>
            <a href="{{ route('tenant.appointments.index', $currentTenant->slug) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
