@extends('layouts.tenant')

@section('title', 'Order #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))
@section('page-title', 'Test Order #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))
@section('page-subtitle', $order->patient->name ?? '')

@section('topbar-actions')
<div class="flex flex-wrap gap-2">
    @if(in_array($order->status, ['results_ready', 'finalized']))
    @php $reportUrl = route('tenant.orders.report', [$currentTenant->slug, $order]); @endphp
    <div x-data="{ open: false }" class="inline-block">
        <button @click="open = true" type="button" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download Report
        </button>

        {{-- Options popup (teleported to body so it escapes the header's backdrop-filter containing block) --}}
        <template x-teleport="body">
        <div x-show="open" x-cloak @keydown.escape.window="open = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(15,23,42,0.45);"
             x-transition.opacity>
            <div @click.away="open = false"
                 class="w-full max-w-md rounded-2xl p-6"
                 style="background:#fff; box-shadow:0 20px 60px rgba(0,0,0,0.25);">
                <div class="flex items-start justify-between mb-1">
                    <h3 class="font-semibold text-base" style="color:#1e293b;">Download Report</h3>
                    <button @click="open = false" style="color:#94a3b8; background:none; border:none; cursor:pointer;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="text-sm mb-4" style="color:#64748b;">Choose what to include — omit the header and/or footer when printing on pre-printed letterhead. The report opens in a new tab.</p>

                <div class="grid grid-cols-1 gap-2">
                    <a href="{{ $reportUrl }}?header=1&footer=1" target="_blank" @click="open = false"
                       class="flex items-center gap-3 p-3 rounded-xl border text-sm transition-all"
                       style="border-color:rgba(0,0,0,0.1); color:#1e293b;"
                       onmouseover="this.style.background='rgba(99,102,241,0.08)'; this.style.borderColor='rgba(99,102,241,0.3)';"
                       onmouseout="this.style.background=''; this.style.borderColor='rgba(0,0,0,0.1)';">
                        <span class="font-medium">With header &amp; footer</span>
                        <span class="ml-auto text-xs" style="color:#94a3b8;">full report</span>
                    </a>
                    <a href="{{ $reportUrl }}?header=0&footer=1" target="_blank" @click="open = false"
                       class="flex items-center gap-3 p-3 rounded-xl border text-sm transition-all"
                       style="border-color:rgba(0,0,0,0.1); color:#1e293b;"
                       onmouseover="this.style.background='rgba(99,102,241,0.08)'; this.style.borderColor='rgba(99,102,241,0.3)';"
                       onmouseout="this.style.background=''; this.style.borderColor='rgba(0,0,0,0.1)';">
                        <span class="font-medium">Without header</span>
                        <span class="ml-auto text-xs" style="color:#94a3b8;">letterhead has header</span>
                    </a>
                    <a href="{{ $reportUrl }}?header=1&footer=0" target="_blank" @click="open = false"
                       class="flex items-center gap-3 p-3 rounded-xl border text-sm transition-all"
                       style="border-color:rgba(0,0,0,0.1); color:#1e293b;"
                       onmouseover="this.style.background='rgba(99,102,241,0.08)'; this.style.borderColor='rgba(99,102,241,0.3)';"
                       onmouseout="this.style.background=''; this.style.borderColor='rgba(0,0,0,0.1)';">
                        <span class="font-medium">Without footer</span>
                        <span class="ml-auto text-xs" style="color:#94a3b8;">omits signature too</span>
                    </a>
                    <a href="{{ $reportUrl }}?header=0&footer=0" target="_blank" @click="open = false"
                       class="flex items-center gap-3 p-3 rounded-xl border text-sm transition-all"
                       style="border-color:rgba(0,0,0,0.1); color:#1e293b;"
                       onmouseover="this.style.background='rgba(99,102,241,0.08)'; this.style.borderColor='rgba(99,102,241,0.3)';"
                       onmouseout="this.style.background=''; this.style.borderColor='rgba(0,0,0,0.1)';">
                        <span class="font-medium">Without both</span>
                        <span class="ml-auto text-xs" style="color:#94a3b8;">body only</span>
                    </a>
                </div>
            </div>
        </div>
        </template>
    </div>
    @endif
    @if(!in_array($order->status, ['finalized', 'cancelled']))
    <form method="POST" action="{{ route('tenant.orders.status', [$currentTenant->slug, $order]) }}">
        @csrf @method('PATCH')
        @php
        $nextLabels = ['pending' => 'Mark Sample Collected', 'sample_collected' => 'Mark Processing', 'processing' => 'Mark Results Ready', 'results_ready' => 'Finalize Order'];
        $nextLabel  = $nextLabels[$order->status] ?? null;
        @endphp
        @if($nextLabel)
        <button type="submit" class="btn-primary text-sm">{{ $nextLabel }}</button>
        @endif
    </form>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Order info sidebar --}}
    <div class="space-y-5">
        <div class="glass-card p-6 space-y-3">
            <h4 class="text-white font-medium mb-4">Order Summary</h4>
            @php
            $info = [
                'Patient'    => '<a href="' . route('tenant.patients.show', [$currentTenant->slug, $order->patient_id]) . '" class="text-indigo-300 hover:underline">' . e($order->patient->name) . '</a>',
                'Created By' => $order->branch
                    ? '<span class="badge badge-success">' . e($order->branch->name) . ' (Branch)</span>'
                    : e($order->createdBy->name ?? '—'),
                'Created At' => $order->created_at->format('d M Y, H:i'),
                'Status'     => '<span class="badge badge-' . $order->status_color . '">' . $order->status_label . '</span>',
            ];
            @endphp
            @foreach($info as $label => $val)
            <div class="flex items-start justify-between gap-3 text-sm">
                <span class="text-white/40 flex-shrink-0">{{ $label }}</span>
                <span class="text-right">{!! $val !!}</span>
            </div>
            @endforeach

            @if($order->sample_collected_at)
            <div class="flex justify-between text-sm"><span class="text-white/40">Sample Collected</span><span class="text-white/60 text-right">{{ $order->sample_collected_at->format('d M Y, H:i') }}</span></div>
            @endif
            @if($order->results_ready_at)
            <div class="flex justify-between text-sm"><span class="text-white/40">Results Ready</span><span class="text-white/60 text-right">{{ $order->results_ready_at->format('d M Y, H:i') }}</span></div>
            @endif
            @if($order->finalized_at)
            <div class="flex justify-between text-sm"><span class="text-white/40">Finalized</span><span class="text-white/60 text-right">{{ $order->finalized_at->format('d M Y, H:i') }}</span></div>
            @endif

            @if($order->notes)
            <div class="border-t border-white/10 pt-3">
                <p class="text-white/40 text-xs mb-1">Notes</p>
                <p class="text-white/60 text-sm">{{ $order->notes }}</p>
            </div>
            @endif
        </div>

        @if($order->invoice)
        <div class="glass-card p-6">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-white font-medium">Invoice</h4>
                <a href="{{ route('tenant.billing.show', [$currentTenant->slug, $order->invoice]) }}" class="text-indigo-400 text-xs hover:underline">View &rarr;</a>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-white/40">Total</span><span class="text-white font-medium">{{ number_format($order->invoice->total, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-white/40">Status</span>
                    <span class="badge badge-{{ $order->invoice->status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($order->invoice->status) }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Test items & result entry --}}
    <div class="lg:col-span-2 space-y-4">
        <h4 class="text-white font-medium">Tests & Results</h4>

        @php
            // Group items by the panel they came from (null panel = standalone tests)
            $groups = $order->items->groupBy(fn($i) => $i->panel_id ? ($i->panel_name . '#' . $i->panel_id) : '__standalone__');
            $locked = in_array($order->status, ['finalized', 'cancelled']);
        @endphp

        @foreach($groups as $groupKey => $items)
        @php $first = $items->first(); $isPanel = $first->panel_id !== null; @endphp

        <div class="space-y-3">
            {{-- Panel heading --}}
            @if($isPanel)
            <div class="flex items-center gap-2 pt-2">
                <div class="w-7 h-7 rounded-lg bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h5 class="text-white font-semibold text-sm">{{ $first->panel_name }}</h5>
                <span class="text-white/30 text-xs">Panel · {{ $items->count() }} tests</span>
            </div>
            @endif

            @php $lastHeader = '__none__'; @endphp
            @foreach($items as $item)
                {{-- Section sub-header within a panel --}}
                @if($isPanel && $item->section_header && $item->section_header !== $lastHeader)
                <p class="text-purple-300/80 text-xs uppercase tracking-wider pt-2 pl-1">{{ $item->section_header }}</p>
                @endif
                @php $lastHeader = $item->section_header ?? '__none__'; @endphp

                @php $isText = ($item->result_type ?? $item->testCatalog->result_type ?? 'numeric') === 'text'; @endphp

                <div class="glass-card p-5 {{ $isPanel ? 'ml-1' : '' }}" x-data="{ open: {{ $item->result_value ? 'false' : 'true' }} }">
                    <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/15 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-white font-medium text-sm truncate">{{ $item->testCatalog->name }}</p>
                                @if(!$isText && $item->testCatalog->normal_range)
                                <p class="text-white/30 text-xs">Normal: {{ $item->testCatalog->normal_range }} {{ $item->testCatalog->unit }}</p>
                                @elseif($isText)
                                <p class="text-blue-300/60 text-xs">Descriptive result</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($item->result_value)
                            <span class="text-green-400 text-sm font-medium truncate max-w-[160px]">{{ $item->result_value }} {{ $isText ? '' : $item->testCatalog->unit }}</span>
                            @endif
                            <span class="badge badge-{{ $item->status === 'completed' ? 'success' : 'gray' }}">{{ ucfirst($item->status) }}</span>
                            <svg class="w-4 h-4 text-white/30 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    <div x-show="open" x-transition class="mt-4 border-t border-white/10 pt-4">
                        @if(!$locked)
                        <form method="POST" action="{{ route('tenant.orders.result', [$currentTenant->slug, $order, $item]) }}"
                              enctype="multipart/form-data" class="space-y-3">
                            @csrf @method('PATCH')
                            @if($isText)
                            <div>
                                <label class="form-label text-xs">Result (free text)</label>
                                <textarea name="result_value" class="glass-input text-sm" rows="3"
                                          placeholder="e.g. Microcytic hypochromic, anisocytosis +">{{ $item->result_value }}</textarea>
                            </div>
                            @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label text-xs">Result Value</label>
                                    <input type="text" name="result_value" value="{{ $item->result_value }}" class="glass-input text-sm"
                                           placeholder="{{ $item->testCatalog->unit ? 'e.g. 5.2 ' . $item->testCatalog->unit : 'Enter result...' }}">
                                </div>
                                <div>
                                    <label class="form-label text-xs">Attach File (optional)</label>
                                    <input type="file" name="result_file" class="glass-input text-xs py-2.5" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                            </div>
                            @endif
                            <div>
                                <label class="form-label text-xs">Remarks / Interpretation</label>
                                <textarea name="remarks" class="glass-input text-sm" rows="2"
                                          placeholder="Abnormal findings, notes…">{{ $item->remarks }}</textarea>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="submit" class="btn-primary text-xs py-2 px-4">Save Result</button>
                                @if($item->entered_by)
                                <span class="text-white/30 text-xs">Last updated by {{ $item->enteredBy->name }} {{ $item->entered_at?->diffForHumans() }}</span>
                                @endif
                            </div>
                        </form>
                        @else
                        <div class="space-y-2 text-sm">
                            <div class="flex gap-4">
                                <span class="text-white/40">Result:</span>
                                <span class="text-white">{{ $item->result_value ?? '—' }} {{ $isText ? '' : $item->testCatalog->unit }}</span>
                            </div>
                            @if($item->remarks)
                            <div class="flex gap-4">
                                <span class="text-white/40">Remarks:</span>
                                <span class="text-white/70">{{ $item->remarks }}</span>
                            </div>
                            @endif
                            @if($item->result_file)
                            <a href="{{ asset('storage/' . $item->result_file) }}" target="_blank" class="text-indigo-400 text-xs hover:underline">View Attached File</a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
@endsection
