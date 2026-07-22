<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\LabBank;
use App\Services\PdfGenerator;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request, string $lab_slug)
    {
        $query = Invoice::with('patient');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('patient', fn($q2) => $q2->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();
        return view('tenant.billing.index', compact('invoices'));
    }

    public function create(string $lab_slug)
    {
        // Invoices are auto-generated from test orders; manual creation not supported
        return redirect()->route('tenant.billing.index', $lab_slug)
                         ->with('error', 'Invoices are generated automatically when a test order is created.');
    }

    public function store(Request $request, string $lab_slug)
    {
        return $this->create($lab_slug);
    }

    public function show(string $lab_slug, Invoice $invoice)
    {
        $invoice->load(['patient', 'items', 'testOrder', 'payments.bank']);
        $banks = LabBank::active()->get();
        return view('tenant.billing.show', compact('invoice', 'banks'));
    }

    public function edit(string $lab_slug, Invoice $invoice)
    {
        return view('tenant.billing.edit', compact('invoice'));
    }

    public function update(Request $request, string $lab_slug, Invoice $invoice)
    {
        $data = $request->validate([
            'discount' => 'nullable|numeric|min:0|max:' . $invoice->subtotal,
            'notes'    => 'nullable|string|max:1000',
        ]);

        $discount = (float) ($data['discount'] ?? 0);
        $invoice->update([
            'discount' => $discount,
            'total'    => max(0, $invoice->subtotal - $discount),
            'notes'    => $data['notes'],
        ]);

        return redirect()->route('tenant.billing.show', [$lab_slug, $invoice])
                         ->with('success', 'Invoice updated.');
    }

    public function destroy(string $lab_slug, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Paid invoices cannot be deleted.');
        }
        $invoice->delete();
        return redirect()->route('tenant.billing.index', $lab_slug)
                         ->with('success', 'Invoice deleted.');
    }

    public function addPayment(Request $request, string $lab_slug, Invoice $invoice)
    {
        $data = $request->validate([
            'method'  => 'required|in:cash,bank',
            'bank_id' => 'required_if:method,bank|nullable|exists:lab_banks,id',
            'amount'  => 'required|numeric|min:0.01|max:' . $invoice->balance,
            'notes'   => 'nullable|string|max:500',
            'paid_at' => 'nullable|date',
        ]);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'This invoice is already fully paid.');
        }

        InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'method'     => $data['method'],
            'bank_id'    => $data['method'] === 'bank' ? $data['bank_id'] : null,
            'amount'     => $data['amount'],
            'notes'      => $data['notes'] ?? null,
            'paid_at'    => $data['paid_at'] ?? now(),
            'created_by' => auth()->id(),
        ]);

        $invoice->syncPaymentTotals();

        return back()->with('success', 'Payment of ' . number_format($data['amount'], 2) . ' recorded.');
    }

    public function deletePayment(string $lab_slug, Invoice $invoice, InvoicePayment $payment)
    {
        if ((int) $payment->invoice_id !== $invoice->id) {
            abort(403);
        }

        $payment->delete();
        $invoice->syncPaymentTotals();

        return back()->with('success', 'Payment entry removed and invoice totals updated.');
    }

    // Keep for backward compatibility — now delegates to addPayment
    public function markPaid(Request $request, string $lab_slug, Invoice $invoice)
    {
        return $this->addPayment($request, $lab_slug, $invoice);
    }

    public function downloadPdf(string $lab_slug, Invoice $invoice)
    {
        $pdf      = PdfGenerator::invoice($invoice);
        $filename = 'invoice-' . $invoice->invoice_number . '.pdf';
        return $pdf->download($filename);
    }
}
