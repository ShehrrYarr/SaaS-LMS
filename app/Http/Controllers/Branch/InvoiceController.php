<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\LabBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    private function branchId(): int
    {
        return Auth::guard('branch')->id();
    }

    /** Invoices are in scope when they belong to one of this branch's customers. */
    private function authorizeInvoice(Invoice $invoice): void
    {
        abort_unless(
            $invoice->patient && $invoice->patient->branch_id === $this->branchId(),
            403
        );
    }

    public function index(Request $request, string $lab_slug)
    {
        $branchId = $this->branchId();

        $query = Invoice::with('patient')
            ->whereHas('patient', fn ($q) => $q->where('branch_id', $branchId));

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('patient', fn ($q2) => $q2->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();
        return view('branch.invoices.index', compact('invoices'));
    }

    public function show(string $lab_slug, Invoice $invoice)
    {
        $invoice->load('patient');
        $this->authorizeInvoice($invoice);

        $invoice->load(['items', 'testOrder', 'payments.bank']);
        $banks = LabBank::active()->get();

        return view('branch.invoices.show', compact('invoice', 'banks'));
    }

    public function addPayment(Request $request, string $lab_slug, Invoice $invoice)
    {
        $invoice->load('patient');
        $this->authorizeInvoice($invoice);

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
            'notes'      => trim('Recorded by branch: ' . Auth::guard('branch')->user()->name . '. ' . ($data['notes'] ?? '')),
            'paid_at'    => $data['paid_at'] ?? now(),
            'created_by' => null,
        ]);

        $invoice->syncPaymentTotals();

        return back()->with('success', 'Payment of ' . number_format($data['amount'], 2) . ' recorded.');
    }
}
