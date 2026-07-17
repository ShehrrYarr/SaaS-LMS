@extends('layouts.tenant')

@section('title', 'Edit Test')
@section('page-title', 'Edit Test')
@section('page-subtitle', $test->name)

@section('topbar-actions')
<a href="{{ route('tenant.tests.index', $currentTenant->slug) }}" class="btn-secondary text-sm">
    &larr; Back to Catalog
</a>
@endsection

@section('content')
<div class="max-w-2xl" x-data="testForm({
    availableTests: {{ $availableTests->map(fn($t) => ['id'=>$t->id,'name'=>$t->name,'code'=>$t->code ?? '','category'=>$t->category ?? '','price'=>$t->price,'unit'=>$t->unit ?? '','result_type'=>$t->result_type ?? 'numeric'])->values()->toJson() }},
    availablePanels: {{ $availablePanels->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'code'=>$p->code ?? '','category'=>$p->category ?? '','price'=>$p->price,'tests_count'=>$p->tests_count])->values()->toJson() }},
    initialLayout: {{ json_encode(old('panel_layout') ? json_decode(old('panel_layout'), true) : $layout) }},
    initialIsPanel: {{ $test->is_panel ? 'true' : 'false' }},
    initialResultType: '{{ old('result_type', $test->result_type ?? 'numeric') }}'
})">
    <form method="POST" action="{{ route('tenant.tests.update', [$currentTenant->slug, $test]) }}" class="space-y-6">
        @csrf @method('PUT')
        <input type="hidden" name="panel_layout" :value="JSON.stringify(layout)">

        <div class="glass-card p-8 space-y-5">
            <div class="flex items-center gap-3 pb-4 border-b border-white/10">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                     style="background: {{ $test->is_panel ? 'rgba(139,92,246,0.2)' : 'rgba(99,102,241,0.2)' }};">
                    <svg class="w-4 h-4 {{ $test->is_panel ? 'text-purple-400' : 'text-indigo-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($test->is_panel)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        @endif
                    </svg>
                </div>
                <div>
                    <p class="text-white font-medium">{{ $test->is_panel ? 'Test Panel' : 'Individual Test' }}</p>
                    <p class="text-white/30 text-xs">Type cannot be changed after creation</p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <label class="form-label mb-0 text-xs">Active</label>
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked($test->is_active)
                           class="rounded border-white/20 bg-white/10 text-indigo-500 focus:ring-indigo-500/50">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="form-label">Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $test->name) }}" class="glass-input" required>
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Short Code</label>
                    <input type="text" name="code" value="{{ old('code', $test->code) }}" class="glass-input" maxlength="50">
                </div>

                <div>
                    <label class="form-label">{{ $test->is_panel ? 'Panel Price (bundled)' : 'Price' }} <span class="text-red-400">*</span></label>
                    <input type="number" name="price" value="{{ old('price', $test->price) }}" class="glass-input"
                           min="0" step="0.01" required>
                    @error('price')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Category</label>
                    <div class="flex gap-2">
                        <div x-show="!newCategory" class="flex-1">
                            <select name="category" class="glass-input">
                                <option value="">None</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat }}" @selected(old('category', $test->category) === $cat)>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="newCategory" class="flex-1" style="display:none">
                            <input type="text" name="category" value="{{ old('category', $test->category) }}" class="glass-input">
                        </div>
                        <button type="button" @click="newCategory = !newCategory"
                                class="text-indigo-400 text-xs whitespace-nowrap hover:text-indigo-300">
                            <span x-text="newCategory ? 'Pick existing' : '+ New'"></span>
                        </button>
                    </div>
                </div>

                {{-- Individual test: result type + numeric fields --}}
                @if(!$test->is_panel)
                <div class="sm:col-span-2">
                    <label class="form-label">Result Type</label>
                    <div class="flex gap-2">
                        <button type="button" @click="resultType = 'numeric'"
                                :style="resultType === 'numeric' ? 'background:rgba(99,102,241,0.12); color:#6366f1; border-color:rgba(99,102,241,0.3);' : 'color:#64748b; border-color:rgba(255,255,255,0.1);'"
                                class="flex-1 px-3 py-2 rounded-xl text-sm font-medium border transition-all">
                            Numeric (value + range)
                        </button>
                        <button type="button" @click="resultType = 'text'"
                                :style="resultType === 'text' ? 'background:rgba(99,102,241,0.12); color:#6366f1; border-color:rgba(99,102,241,0.3);' : 'color:#64748b; border-color:rgba(255,255,255,0.1);'"
                                class="flex-1 px-3 py-2 rounded-xl text-sm font-medium border transition-all">
                            Descriptive (free text)
                        </button>
                    </div>
                    <input type="hidden" name="result_type" :value="resultType">
                </div>
                <div x-show="resultType === 'numeric'">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" value="{{ old('unit', $test->unit) }}" class="glass-input"
                           placeholder="e.g. mg/dL">
                </div>
                <div x-show="resultType === 'numeric'">
                    <label class="form-label">Normal Range</label>
                    <input type="text" name="normal_range" value="{{ old('normal_range', $test->normal_range) }}" class="glass-input"
                           placeholder="e.g. 4.5–11.0">
                </div>
                @endif
            </div>

            <div>
                <label class="form-label">Description / Instructions</label>
                <textarea name="description" class="glass-input" rows="3"
                          placeholder="Optional instructions, sample type, or notes…">{{ old('description', $test->description) }}</textarea>
            </div>
        </div>

        {{-- ── Panel builder ── --}}
        @if($test->is_panel)
        <div class="glass-card p-6 space-y-5">
            <div>
                <h3 class="text-white font-semibold">Panel Layout</h3>
                <p class="text-white/40 text-sm mt-1">Add tests and section headers, then arrange them top-to-bottom. This is exactly how the report will be laid out.</p>
            </div>

            <div class="space-y-2">
                <template x-if="layout.length === 0">
                    <p class="text-white/30 text-sm text-center py-4 rounded-xl border border-dashed border-white/10">
                        No items yet — add a header or tests below.
                    </p>
                </template>

                <template x-for="(row, i) in layout" :key="row._k">
                    <div class="flex items-center gap-2 p-3 rounded-xl border"
                         :style="row.type === 'header' ? 'background:rgba(139,92,246,0.08); border-color:rgba(139,92,246,0.25);'
                               : row.type === 'panel' ? 'background:rgba(139,92,246,0.14); border-color:rgba(139,92,246,0.4);'
                               : 'background:rgba(255,255,255,0.03); border-color:rgba(255,255,255,0.08);'">
                        <div class="flex flex-col gap-0.5 flex-shrink-0">
                            <button type="button" @click="moveUp(i)" :disabled="i === 0"
                                    class="text-white/40 hover:text-white disabled:opacity-20" title="Move up">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                            </button>
                            <button type="button" @click="moveDown(i)" :disabled="i === layout.length - 1"
                                    class="text-white/40 hover:text-white disabled:opacity-20" title="Move down">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>

                        <template x-if="row.type === 'header'">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <span class="text-purple-300 text-xs uppercase tracking-wider flex-shrink-0">Header</span>
                                <input type="text" x-model="row.label" placeholder="Section header, e.g. Diff. Leuc. Count"
                                       class="flex-1 bg-transparent text-white text-sm font-semibold border-0 border-b border-white/10 focus:border-purple-400 focus:ring-0 px-0 py-1">
                            </div>
                        </template>

                        <template x-if="row.type === 'test'">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <span class="text-white text-sm truncate" x-text="testName(row.id)"></span>
                                <span class="text-white/30 text-xs flex-shrink-0" x-text="testMeta(row.id)"></span>
                            </div>
                        </template>

                        {{-- nested panel row --}}
                        <template x-if="row.type === 'panel'">
                            <div class="flex items-center gap-2 flex-1 min-w-0">
                                <span class="text-xs px-2 py-0.5 rounded-full uppercase tracking-wider flex-shrink-0"
                                      style="background:rgba(139,92,246,0.25); color:#c4b5fd;">Panel</span>
                                <span class="text-white text-sm font-semibold truncate" x-text="panelName(row.id)"></span>
                                <span class="text-white/30 text-xs flex-shrink-0" x-text="panelMeta(row.id)"></span>
                            </div>
                        </template>

                        <button type="button" @click="removeItem(i)" class="text-white/40 hover:text-red-400 flex-shrink-0" title="Remove">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            <div class="flex items-center gap-2">
                <input type="text" x-model="newHeader" @keydown.enter.prevent="addHeader()"
                       placeholder="New section header…" class="glass-input text-sm flex-1">
                <button type="button" @click="addHeader()" class="btn-secondary text-sm whitespace-nowrap">+ Add Header</button>
            </div>

            <div class="border-t border-white/10 pt-4">
                <label class="form-label mb-2 block">Add Tests</label>
                @if($availableTests->isEmpty())
                <p class="text-white/30 text-sm">No individual tests available.</p>
                @else
                <div class="relative mb-2">
                    <input type="text" x-model="testSearch" placeholder="Search tests by name, code, category…" class="glass-input text-sm pl-9">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none" style="color:#64748b;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                    </div>
                </div>
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    <template x-for="t in filteredTests" :key="t.id">
                        <button type="button" @click="addTest(t.id)" :disabled="hasTest(t.id)"
                                class="w-full flex items-center gap-3 p-3 rounded-xl border text-left transition-all"
                                :style="hasTest(t.id) ? 'opacity:0.4; cursor:not-allowed; border-color:rgba(255,255,255,0.08);' : 'border-color:rgba(255,255,255,0.08); cursor:pointer;'">
                            <svg width="16" height="16" class="flex-shrink-0" :style="hasTest(t.id) ? 'color:#22c55e' : 'color:#6366f1'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!hasTest(t.id)" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                <path x-show="hasTest(t.id)" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="flex-1 text-white text-sm" x-text="t.name"></span>
                            <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0"
                                  x-show="t.result_type === 'text'" style="background:rgba(59,130,246,0.15); color:#60a5fa;">text</span>
                            <span class="text-white/30 text-xs flex-shrink-0" x-text="parseFloat(t.price).toFixed(2)"></span>
                        </button>
                    </template>
                    <template x-if="filteredTests.length === 0">
                        <p class="text-white/30 text-sm text-center py-3">No tests match.</p>
                    </template>
                </div>
                @endif
            </div>

            {{-- Add panels (nest an existing panel as a sub-section) --}}
            @if($isNestedElsewhere)
            <div class="border-t border-white/10 pt-4">
                <p class="text-white/30 text-sm">This panel is used inside another panel, so it can't contain panels itself.</p>
            </div>
            @elseif($availablePanels->isNotEmpty())
            <div class="border-t border-white/10 pt-4">
                <label class="form-label mb-2 block">Add Panels</label>
                <p class="text-white/30 text-xs mb-2">A nested panel appears as a sub-section on the report. Its tests are included; its own price is ignored — this panel's bundled price covers everything.</p>
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    <template x-for="p in filteredPanels" :key="'p' + p.id">
                        <button type="button" @click="addPanel(p.id)" :disabled="hasPanel(p.id)"
                                class="w-full flex items-center gap-3 p-3 rounded-xl border text-left transition-all"
                                :style="hasPanel(p.id) ? 'opacity:0.4; cursor:not-allowed; border-color:rgba(139,92,246,0.2);' : 'border-color:rgba(139,92,246,0.25); cursor:pointer;'">
                            <svg width="16" height="16" class="flex-shrink-0" :style="hasPanel(p.id) ? 'color:#22c55e' : 'color:#a78bfa'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!hasPanel(p.id)" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                <path x-show="hasPanel(p.id)" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="flex-1 text-white text-sm" x-text="p.name"></span>
                            <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0"
                                  style="background:rgba(139,92,246,0.15); color:#c4b5fd;" x-text="p.tests_count + ' tests'"></span>
                        </button>
                    </template>
                    <template x-if="filteredPanels.length === 0">
                        <p class="text-white/30 text-sm text-center py-3">No panels match.</p>
                    </template>
                </div>
            </div>
            @endif
        </div>
        @endif

        @if($errors->any())
        <div class="glass-card p-4 border-red-500/30 bg-red-500/10">
            <ul class="list-disc list-inside text-red-400 text-sm space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('tenant.tests.index', $currentTenant->slug) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@include('tenant.tests._form-script')
@endsection
