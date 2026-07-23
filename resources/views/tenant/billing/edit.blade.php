@extends('layouts.tenant')

@section('title', 'Edit Invoice')
@section('page-title', 'Edit Invoice')
@section('page-subtitle', $invoice->invoice_number)

@section('topbar-actions')
<a href="{{ route('tenant.billing.show', [$currentTenant->slug, $invoice]) }}" class="btn-secondary text-sm">&larr; Back</a>
@endsection

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('tenant.billing.update', [$currentTenant->slug, $invoice]) }}" class="space-y-6">
        @csrf @method('PUT')
        <div class="glass-card p-8 space-y-5">
            <div>
                <label class="form-label">Discount</label>
                <input type="number" name="discount" value="{{ old('discount', $invoice->discount) }}"
                       class="glass-input" min="0" step="0.01" max="{{ $invoice->subtotal }}">
                <p class="text-white/30 text-xs mt-1">Subtotal: {{ money($invoice->subtotal) }}</p>
                @error('discount')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" class="glass-input" rows="3">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('tenant.billing.show', [$currentTenant->slug, $invoice]) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
