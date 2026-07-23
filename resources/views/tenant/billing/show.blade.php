@extends('layouts.tenant')

@section('title', $invoice->invoice_number)
@section('page-title', 'Invoice ' . $invoice->invoice_number)
@section('page-subtitle', $invoice->patient->name ?? '')

@section('topbar-actions')
<div class="flex gap-2">
    <a href="{{ route('tenant.billing.pdf', [$currentTenant->slug, $invoice]) }}" class="btn-secondary text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Download PDF
    </a>
    <a href="{{ route('tenant.billing.edit', [$currentTenant->slug, $invoice]) }}" class="btn-secondary text-sm">Edit</a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Invoice details --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="glass-card p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="font-bold text-xl" style="color:#1e293b;">{{ $invoice->invoice_number }}</h3>
                    <p class="text-sm" style="color:#94a3b8;">{{ $invoice->created_at->format('d M Y') }}</p>
                </div>
                <span class="badge badge-{{ $invoice->status_color }} text-sm px-3 py-1">{{ ucfirst($invoice->status) }}</span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                <div>
                    <p class="text-xs uppercase tracking-wider mb-1" style="color:#94a3b8;">Bill To</p>
                    <p class="font-medium" style="color:#1e293b;">{{ $invoice->patient->name }}</p>
                    <p style="color:#64748b;">{{ $invoice->patient->email }}</p>
                    <p style="color:#64748b;">{{ $invoice->patient->phone }}</p>
                </div>
                @if($invoice->testOrder)
                <div>
                    <p class="text-xs uppercase tracking-wider mb-1" style="color:#94a3b8;">Test Order</p>
                    <a href="{{ route('tenant.orders.show', [$currentTenant->slug, $invoice->testOrder]) }}"
                       class="font-medium" style="color:#6366f1;">#{{ str_pad($invoice->testOrder->id, 6, '0', STR_PAD_LEFT) }}</a>
                    <p style="color:#64748b;">{{ $invoice->testOrder->created_at->format('d M Y') }}</p>
                </div>
                @endif
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.08);">
                        <th class="text-left py-2 font-medium" style="color:#94a3b8;">Description</th>
                        <th class="text-center py-2 font-medium w-16" style="color:#94a3b8;">Qty</th>
                        <th class="text-right py-2 font-medium w-24" style="color:#94a3b8;">Unit Price</th>
                        <th class="text-right py-2 font-medium w-24" style="color:#94a3b8;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.04);">
                        <td class="py-3" style="color:#1e293b;">{{ $item->description }}</td>
                        <td class="py-3 text-center" style="color:#64748b;">{{ $item->quantity }}</td>
                        <td class="py-3 text-right" style="color:#64748b;">{{ money($item->unit_price) }}</td>
                        <td class="py-3 text-right font-medium" style="color:#1e293b;">{{ money($item->total) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="py-2 text-right" style="color:#94a3b8;">Subtotal</td>
                        <td class="py-2 text-right" style="color:#1e293b;">{{ money($invoice->subtotal) }}</td>
                    </tr>
                    @if($invoice->discount > 0)
                    <tr>
                        <td colspan="3" class="py-1 text-right" style="color:#94a3b8;">Discount</td>
                        <td class="py-1 text-right" style="color:#ef4444;">-{{ money($invoice->discount) }}</td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid rgba(0,0,0,0.1);">
                        <td colspan="3" class="py-2 text-right font-bold" style="color:#1e293b;">Total</td>
                        <td class="py-2 text-right font-bold text-lg" style="color:#1e293b;">{{ money($invoice->total) }}</td>
                    </tr>
                    @if($invoice->amount_paid > 0)
                    <tr>
                        <td colspan="3" class="py-1 text-right" style="color:#94a3b8;">Amount Paid</td>
                        <td class="py-1 text-right font-medium" style="color:#16a34a;">{{ money($invoice->amount_paid) }}</td>
                    </tr>
                    @if($invoice->balance > 0)
                    <tr>
                        <td colspan="3" class="py-1 text-right" style="color:#94a3b8;">Balance Due</td>
                        <td class="py-1 text-right font-semibold" style="color:#d97706;">{{ money($invoice->balance) }}</td>
                    </tr>
                    @endif
                    @endif
                </tfoot>
            </table>

            @if($invoice->notes)
            <div class="mt-4 pt-4" style="border-top: 1px solid rgba(0,0,0,0.08);">
                <p class="text-xs mb-1" style="color:#94a3b8;">Notes</p>
                <p class="text-sm" style="color:#475569;">{{ $invoice->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Payment History --}}
        <div class="glass-card p-6">
            <h4 class="font-semibold text-sm mb-4" style="color:#1e293b;">Payment History</h4>

            @if($invoice->payments->isEmpty())
            <div class="py-6 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" style="color:#cbd5e1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm" style="color:#94a3b8;">No payments recorded yet.</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($invoice->payments as $payment)
                <div class="flex items-center justify-between p-3 rounded-xl" style="background: rgba(0,0,0,0.03);">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background: {{ $payment->method === 'cash' ? 'rgba(22,163,74,0.1)' : 'rgba(99,102,241,0.1)' }};">
                            @if($payment->method === 'cash')
                            <svg class="w-4 h-4" style="color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                            @else
                            <svg class="w-4 h-4" style="color:#6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium" style="color:#1e293b;">{{ $payment->method_label }}</p>
                            <p class="text-xs" style="color:#94a3b8;">{{ $payment->paid_at->format('d M Y, H:i') }}
                                @if($payment->notes) &middot; {{ $payment->notes }}@endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-semibold text-sm" style="color:#1e293b;">{{ money($payment->amount) }}</span>
                        <form method="POST"
                              action="{{ route('tenant.billing.payment.delete', [$currentTenant->slug, $invoice, $payment]) }}"
                              onsubmit="return confirm('Remove this payment entry?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-6 h-6 rounded flex items-center justify-center transition-colors"
                                    style="color:#94a3b8;" title="Remove payment"
                                    onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Payment panel --}}
    <div class="space-y-5">
        @if($invoice->status !== 'paid')
        <div class="glass-card p-6" x-data="{ method: 'cash' }">
            <h4 class="font-semibold text-sm mb-1" style="color:#1e293b;">Add Payment</h4>
            @if($invoice->balance > 0)
            <p class="text-xs mb-4" style="color:#94a3b8;">
                Balance due: <span class="font-semibold" style="color:#d97706;">{{ money($invoice->balance) }}</span>
            </p>
            @endif

            <form method="POST" action="{{ route('tenant.billing.payment.add', [$currentTenant->slug, $invoice]) }}" class="space-y-4">
                @csrf

                {{-- Payment method toggle --}}
                <div>
                    <label class="form-label">Payment Method</label>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        <label class="cursor-pointer">
                            <input type="radio" name="method" value="cash" x-model="method" class="sr-only">
                            <div class="text-center py-2 px-3 rounded-xl text-sm font-medium border transition-all cursor-pointer"
                                 :style="method === 'cash'
                                     ? 'background:rgba(22,163,74,0.1); border-color:rgba(22,163,74,0.4); color:#16a34a;'
                                     : 'background:rgba(0,0,0,0.03); border-color:rgba(0,0,0,0.08); color:#64748b;'">
                                Cash
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="method" value="bank" x-model="method" class="sr-only">
                            <div class="text-center py-2 px-3 rounded-xl text-sm font-medium border transition-all cursor-pointer"
                                 :style="method === 'bank'
                                     ? 'background:rgba(99,102,241,0.1); border-color:rgba(99,102,241,0.4); color:#6366f1;'
                                     : 'background:rgba(0,0,0,0.03); border-color:rgba(0,0,0,0.08); color:#64748b;'">
                                Bank
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Bank selector (only when bank selected) --}}
                <div x-show="method === 'bank'" style="display:none;">
                    <label class="form-label">Bank Account</label>
                    @if($banks->isEmpty())
                    <p class="text-sm py-2 px-3 rounded-xl" style="background:rgba(245,158,11,0.1); color:#d97706;">
                        No banks configured.
                        <a href="{{ route('tenant.settings.banks', $currentTenant->slug) }}" style="color:#6366f1; text-decoration:underline;">Add banks in Settings.</a>
                    </p>
                    @else
                    <select name="bank_id" class="glass-input">
                        <option value="">-- Select bank --</option>
                        @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}@if($bank->account_number) ({{ $bank->account_number }})@endif</option>
                        @endforeach
                    </select>
                    @endif
                    @error('bank_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" value="{{ $invoice->balance }}" class="glass-input"
                           min="0.01" step="0.01" max="{{ $invoice->balance }}" required>
                    @error('amount')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Date & Time</label>
                    <input type="datetime-local" name="paid_at"
                           value="{{ now()->format('Y-m-d\TH:i') }}" class="glass-input">
                </div>

                <div>
                    <label class="form-label">Notes <span style="color:#94a3b8;">(optional)</span></label>
                    <input type="text" name="notes" class="glass-input" placeholder="e.g. Cheque no. 1234">
                    @error('notes')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary w-full">Record Payment</button>
            </form>
        </div>
        @else
        <div class="glass-card p-6" style="border: 1px solid rgba(22,163,74,0.2); background: rgba(22,163,74,0.04);">
            <div class="flex items-center gap-3 mb-3">
                <svg class="w-5 h-5" style="color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h4 class="font-medium" style="color:#16a34a;">Fully Paid</h4>
            </div>
            @if($invoice->paid_at)
            <p class="text-sm" style="color:#64748b;">Completed on {{ $invoice->paid_at->format('d M Y, H:i') }}</p>
            @endif
        </div>
        @endif

        <div class="glass-card p-6">
            <h4 class="font-medium text-sm mb-3" style="color:#1e293b;">Quick Links</h4>
            <div class="space-y-2">
                <a href="{{ route('tenant.patients.show', [$currentTenant->slug, $invoice->patient_id]) }}"
                   class="flex items-center gap-2 text-sm transition-colors"
                   style="color:#64748b;" onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    View Patient
                </a>
                @if($invoice->testOrder)
                <a href="{{ route('tenant.orders.show', [$currentTenant->slug, $invoice->testOrder]) }}"
                   class="flex items-center gap-2 text-sm transition-colors"
                   style="color:#64748b;" onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    View Test Order
                </a>
                @endif
                <a href="{{ route('tenant.settings.banks', $currentTenant->slug) }}"
                   class="flex items-center gap-2 text-sm transition-colors"
                   style="color:#64748b;" onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Manage Banks
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
