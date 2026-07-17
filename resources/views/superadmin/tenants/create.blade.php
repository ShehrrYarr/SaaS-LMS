@extends('layouts.superadmin')

@section('title', 'Add Laboratory')
@section('page-title', 'Add Laboratory')
@section('page-subtitle', 'Register a new lab on the platform')

@section('content')
<div class="max-w-2xl">
    <div class="glass-card p-8">
        <form method="POST" action="{{ route('superadmin.tenants.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="sm:col-span-2">
                    <label class="form-label">Laboratory Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="glass-input"
                           placeholder="City Medical Laboratory" required>
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">URL Slug <span class="text-red-400">*</span></label>
                    <div class="flex items-center">
                        <span class="text-white/30 text-sm px-3 py-3 border border-r-0 border-white/12 rounded-l-xl bg-white/5">
                            /
                        </span>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                               class="glass-input rounded-l-none" placeholder="city-medical-lab"
                               pattern="[a-z0-9\-]+" required>
                    </div>
                    <p class="text-white/30 text-xs mt-1">Lowercase, letters, numbers, hyphens only.</p>
                    @error('slug') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Plan <span class="text-red-400">*</span></label>
                    <select name="plan_id" class="glass-input" required>
                        <option value="">Select a plan</option>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    @error('plan_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="glass-input" placeholder="lab@example.com">
                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="glass-input" placeholder="+1-555-0100">
                    @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="glass-input" rows="2" placeholder="123 Health Street, City, State 00000">{{ old('address') }}</textarea>
                    @error('address') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-white/10">
                <button type="submit" class="btn-primary">Create Laboratory</button>
                <a href="{{ route('superadmin.tenants.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Auto-slug from name
    document.querySelector('[name="name"]').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (!slugField.dataset.touched) {
            slugField.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        }
    });
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.touched = '1';
    });
</script>
@endpush
@endsection
