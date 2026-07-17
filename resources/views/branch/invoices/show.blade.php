@extends('layouts.branch')

@section('title', $invoice->invoice_number)
@section('page-title', 'Invoice ' . $invoice->invoice_number)
@section('page-subtitle', $invoice->patient->name ?? '')

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
                    <a href="{{ route('branch.orders.show', [$currentTenant->slug, $invoice->testOrder]) }}"
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
                        <td class="py-3 text-right" style="color:#64748b;">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3 text-right font-medium" style="color:#1e293b;">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="py-2 text-right" style="color:#94a3b8;">Subtotal</td>
                        <td class="py-2 text-right" style="color:#1e293b;">{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->discount > 0)
                    <tr>
                        <td colspan="3" class="py-1 text-right" style="color:#94a3b8;">Discount</td>
                        <td class="py-1 text-right" style="color:#ef4444;">-{{ number_format($invoice->discount, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="border-top: 1px solid rgba(0,0,0,0.1);">
                        <td colspan="3" class="py-2 text-right font-bold" style="color:#1e293b;">Total</td>
                        <td class="py-2 text-right font-bold text-lg" style="color:#1e293b;">{{ number_format($invoice->total, 2) }}</td>
                    </tr>
                    @if($invoice->amount_paid > 0)
                    <tr>
                        <td colspan="3" class="py-1 text-right" style="color:#94a3b8;">Amount Paid</td>
                        <td class="py-1 text-right font-medium" style="color:#16a34a;">{{ number_format($invoice->amount_paid, 2) }}</td>
                    </tr>
                    @if($invoice->balance > 0)
                    <tr>
                        <td colspan="3" class="py-1 text-right" style="color:#94a3b8;">Balance Due</td>
                        <td class="py-1 text-right font-semibold" style="color:#d97706;">{{ number_format($invoice->balance, 2) }}</td>
                    </tr>
                    @endif
                    @endif
                </tfoot>
            </table>
        </div>

        {{-- Payment History (read-only for branches) --}}
        <div class="glass-card p-6">
            <h4 class="font-semibold text-sm mb-4" style="color:#1e293b;">Payment History</h4>

            @if($invoice->payments->isEmpty())
            <p class="text-sm py-4 text-center" style="color:#94a3b8;">No payments recorded yet.</p>
            @else
            <div class="space-y-2">
                @foreach($invoice->payments as $payment)
                <div class="flex items-center justify-between p-3 rounded-xl" style="background: rgba(0,0,0,0.03);">
                    <div>
                        <p class="text-sm font-medium" style="color:#1e293b;">{{ ucfirst($payment->method) }}</p>
                        <p class="text-xs" style="color:#94a3b8;">{{ $payment->paid_at->format('d M Y, H:i') }}
                            @if($payment->notes) &middot; {{ $payment->notes }}@endif
                        </p>
                    </div>
                    <span class="font-semibold text-sm" style="color:#1e293b;">{{ number_format($payment->amount, 2) }}</span>
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
                Balance due: <span class="font-semibold" style="color:#d97706;">{{ number_format($invoice->balance, 2) }}</span>
            </p>
            @endif

            <form method="POST" action="{{ route('branch.invoices.payment.add', [$currentTenant->slug, $invoice]) }}" class="space-y-4">
                @csrf

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

                <div x-show="method === 'bank'" style="display:none;">
                    <label class="form-label">Bank Account</label>
                    @if($banks->isEmpty())
                    <p class="text-sm py-2 px-3 rounded-xl" style="background:rgba(245,158,11,0.1); color:#d97706;">
                        The main lab has not configured any bank accounts.
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
                <a href="{{ route('branch.customers.show', [$currentTenant->slug, $invoice->patient_id]) }}"
                   class="flex items-center gap-2 text-sm transition-colors"
                   style="color:#64748b;" onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    View Customer
                </a>
                @if($invoice->testOrder)
                <a href="{{ route('branch.orders.show', [$currentTenant->slug, $invoice->testOrder]) }}"
                   class="flex items-center gap-2 text-sm transition-colors"
                   style="color:#64748b;" onmouseover="this.style.color='#1e293b'" onmouseout="this.style.color='#64748b'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    View Test Order
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
