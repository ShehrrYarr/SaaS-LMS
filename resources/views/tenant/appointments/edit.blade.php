@extends('layouts.tenant')

@section('title', 'Edit Appointment')
@section('page-title', 'Edit Appointment')
@section('page-subtitle', $appointment->patient->name ?? 'Appointment')

@section('topbar-actions')
<a href="{{ route('tenant.appointments.index', $currentTenant->slug) }}" class="btn-secondary text-sm">&larr; Back</a>
@endsection

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('tenant.appointments.update', [$currentTenant->slug, $appointment]) }}" class="space-y-6">
        @csrf @method('PUT')
        <div class="glass-card p-8 space-y-5">
            <div>
                <label class="form-label">Patient <span class="text-red-400">*</span></label>
                <select name="patient_id" class="glass-input" required>
                    @foreach($patients as $p)
                    <option value="{{ $p->id }}" @selected(old('patient_id', $appointment->patient_id) == $p->id)>
                        {{ $p->name }} ({{ $p->patient_code }})
                    </option>
                    @endforeach
                </select>
                @error('patient_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Date & Time <span class="text-red-400">*</span></label>
                <input type="datetime-local" name="scheduled_at"
                       value="{{ old('scheduled_at', $appointment->scheduled_at->format('Y-m-d\TH:i')) }}"
                       class="glass-input" required>
                @error('scheduled_at')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Status</label>
                <select name="status" class="glass-input">
                    @foreach(['scheduled', 'arrived', 'completed', 'cancelled'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $appointment->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" class="glass-input" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
            </div>
        </div>

        @if($errors->any())
        <div class="glass-card p-4 border-red-500/30 bg-red-500/10">
            <ul class="list-disc list-inside text-red-400 text-sm space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('tenant.appointments.index', $currentTenant->slug) }}" class="btn-secondary">Cancel</a>
            <form method="POST" class="ml-auto" action="{{ route('tenant.appointments.destroy', [$currentTenant->slug, $appointment]) }}"
                  onsubmit="return confirm('Delete this appointment?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
            </form>
        </div>
    </form>
</div>
@endsection
