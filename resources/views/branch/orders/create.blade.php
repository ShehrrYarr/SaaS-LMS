@extends('layouts.branch')

@section('title', 'New Test Order')
@section('page-title', 'New Test Order')
@section('page-subtitle', 'Select tests and assign them to one of your customers')

@section('topbar-actions')
<a href="{{ route('branch.orders.index', $currentTenant->slug) }}" class="btn-secondary text-sm">&larr; Back</a>
@endsection

@section('content')
<div class="max-w-3xl" x-data="{
    /* ── Tests ── */
    selectedTests: {{ json_encode(array_map('intval', (array) old('test_ids', []))) }},
    total: 0,
    testSearch: '',
    tests: {{ $tests->map(fn($t) => ['id'=>$t->id,'name'=>$t->name,'price'=>$t->price,'category'=>$t->category ?? '','is_panel'=>$t->is_panel,'code'=>$t->code ?? ''])->values()->toJson() }},

    /* ── Patients ── */
    patients: {{ $patients->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'code'=>$p->patient_code])->values()->toJson() }},
    patientSearch: '',
    patientOpen: false,
    selectedPatientId: {{ old('patient_id', $patient?->id ?? 'null') }},

    /* ── Init ── */
    init() {
        this.selectedTests.forEach(id => {
            const t = this.tests.find(t => t.id === id);
            if (t) this.total += Number(t.price);
        });
        if (this.selectedPatientId) {
            const p = this.patients.find(p => p.id == this.selectedPatientId);
            if (p) this.patientSearch = p.name + ' (' + p.code + ')';
        }
    },

    /* ── Patient helpers ── */
    get filteredPatients() {
        const q = this.patientSearch.toLowerCase();
        if (!q || this.selectedPatientId) return this.patients;
        return this.patients.filter(p =>
            p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q)
        );
    },
    selectPatient(p) {
        this.selectedPatientId = p.id;
        this.patientSearch = p.name + ' (' + p.code + ')';
        this.patientOpen = false;
    },
    clearPatient() {
        this.selectedPatientId = null;
        this.patientSearch = '';
        this.$nextTick(() => this.$refs.patientInput.focus());
    },

    /* ── Test helpers ── */
    get groupedTests() {
        const q = this.testSearch.trim().toLowerCase();
        const filtered = q
            ? this.tests.filter(t =>
                t.name.toLowerCase().includes(q) ||
                t.category.toLowerCase().includes(q) ||
                t.code.toLowerCase().includes(q))
            : this.tests;
        const groups = {};
        filtered.forEach(t => {
            const cat = t.category || 'Uncategorized';
            if (!groups[cat]) groups[cat] = [];
            groups[cat].push(t);
        });
        return Object.entries(groups).map(([cat, tests]) => ({ cat, tests }));
    },
    toggleTest(id, price) {
        const i = this.selectedTests.indexOf(id);
        if (i === -1) { this.selectedTests.push(id); this.total += Number(price); }
        else { this.selectedTests.splice(i, 1); this.total -= Number(price); }
    },
    isSelected(id) { return this.selectedTests.includes(id); }
}">
    <form method="POST" action="{{ route('branch.orders.store', $currentTenant->slug) }}" class="space-y-6">
        @csrf

        <div class="glass-card p-6 space-y-5">
            {{-- Searchable Customer Dropdown --}}
            <div>
                <label class="form-label">Customer <span class="text-red-400">*</span></label>
                <input type="hidden" name="patient_id" :value="selectedPatientId">

                <div class="relative" @click.away="patientOpen = false">
                    <div class="relative">
                        <input x-ref="patientInput"
                               type="text"
                               x-model="patientSearch"
                               @focus="patientOpen = true; if(selectedPatientId) { selectedPatientId = null; patientSearch = ''; }"
                               @input="patientOpen = true; selectedPatientId = null;"
                               placeholder="Search customer by name or code…"
                               autocomplete="off"
                               class="glass-input pr-10"
                               :class="selectedPatientId ? 'border-indigo-400/50' : ''">
                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none" style="color:#64748b;">
                            <template x-if="selectedPatientId">
                                <button type="button" @click.stop="clearPatient()" class="pointer-events-auto" style="color:#94a3b8; background:none; border:none; cursor:pointer; padding:0;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </template>
                            <template x-if="!selectedPatientId">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </template>
                        </div>
                    </div>

                    <div x-show="patientOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute left-0 right-0 z-50 mt-1 rounded-xl shadow-lg overflow-hidden"
                         style="background:#fff; border:1px solid rgba(0,0,0,0.1); max-height:220px; overflow-y:auto;">

                        <template x-if="filteredPatients.length === 0">
                            <div class="px-4 py-3 text-sm" style="color:#94a3b8;">
                                No customers found
                                <a href="{{ route('branch.customers.create', $currentTenant->slug) }}"
                                   class="ml-2 text-indigo-500 underline">Register customer</a>
                            </div>
                        </template>

                        <template x-for="p in filteredPatients" :key="p.id">
                            <div @click="selectPatient(p)"
                                 class="px-4 py-2.5 cursor-pointer text-sm flex items-center gap-2 transition-colors"
                                 :style="selectedPatientId == p.id ? 'background:#eef2ff;' : ''"
                                 onmouseover="if(!this.style.background.includes('eef')) this.style.background='#f8fafc';"
                                 onmouseout="if(!this.style.background.includes('eef')) this.style.background='';">
                                <span style="color:#1e293b;" x-text="p.name"></span>
                                <span style="color:#94a3b8; font-size:11px;" x-text="'(' + p.code + ')'"></span>
                                <template x-if="selectedPatientId == p.id">
                                    <svg class="ml-auto flex-shrink-0" width="14" height="14" fill="none" stroke="#6366f1" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                @error('patient_id')<p class="form-error mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" class="glass-input" rows="2" placeholder="Clinical notes, priority, etc.">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Test Selection --}}
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold" style="color:#1e293b;">Select Tests</h3>
                <div class="text-right">
                    <p class="text-xs" style="color:#94a3b8;"><span x-text="selectedTests.length"></span> selected</p>
                    <p class="font-semibold" style="color:#1e293b;">Total: <span x-text="'PKR ' + Math.round(total).toLocaleString()"></span></p>
                </div>
            </div>

            {{-- Test search --}}
            <div class="relative mb-4">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none" style="color:#64748b;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </div>
                <input type="text"
                       x-model="testSearch"
                       placeholder="Search tests by name, code or category…"
                       class="glass-input pl-9 text-sm">
                <button x-show="testSearch" type="button" @click="testSearch=''"
                        class="absolute inset-y-0 right-3 flex items-center"
                        style="background:none; border:none; cursor:pointer; color:#64748b;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @error('test_ids')<p class="form-error mb-3">{{ $message }}</p>@enderror

            <div class="space-y-6">
                <template x-if="groupedTests.length === 0">
                    <p class="text-sm text-center py-6" style="color:#94a3b8;">No tests match "<span x-text="testSearch"></span>"</p>
                </template>

                <template x-for="group in groupedTests" :key="group.cat">
                    <div>
                        <p class="text-xs uppercase tracking-wider mb-2" style="color:#94a3b8;" x-text="group.cat"></p>
                        <div class="space-y-2">
                            <template x-for="test in group.tests" :key="test.id">
                                <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                                       :style="isSelected(test.id)
                                           ? 'background:rgba(99,102,241,0.10); border-color:rgba(99,102,241,0.30);'
                                           : 'border-color:rgba(0,0,0,0.08);'">
                                    <input type="checkbox"
                                           :name="'test_ids[]'"
                                           :value="test.id"
                                           :checked="isSelected(test.id)"
                                           @change="toggleTest(test.id, test.price)"
                                           class="rounded flex-shrink-0"
                                           style="accent-color:#6366f1;">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm font-medium" style="color:#1e293b;" x-text="test.name"></span>
                                        <template x-if="test.is_panel">
                                            <span class="ml-2 text-xs px-2 py-0.5 rounded-full" style="background:rgba(139,92,246,0.15); color:#7c3aed;">Panel</span>
                                        </template>
                                        <template x-if="test.code">
                                            <span class="text-xs ml-1" style="color:#94a3b8;" x-text="test.code"></span>
                                        </template>
                                    </div>
                                    <span class="text-sm font-medium flex-shrink-0" style="color:#64748b;" x-text="'PKR ' + Math.round(parseFloat(test.price)).toLocaleString()"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                @if($tests->isEmpty())
                <p class="text-sm text-center py-6" style="color:#94a3b8;">The laboratory has no active tests in its catalog yet.</p>
                @endif
            </div>
        </div>

        <div x-show="selectedTests.length > 0" class="glass-card p-4 border-indigo-500/20 bg-indigo-500/5">
            <p class="text-sm" style="color:#64748b;"><span class="font-semibold" style="color:#1e293b;" x-text="selectedTests.length"></span> test(s) selected — an invoice for <strong x-text="'PKR ' + Math.round(total).toLocaleString()"></strong> will be auto-generated.</p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary" :disabled="selectedTests.length === 0">Create Order</button>
            <a href="{{ route('branch.orders.index', $currentTenant->slug) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
