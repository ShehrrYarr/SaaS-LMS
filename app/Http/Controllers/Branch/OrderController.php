<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\TestCatalog;
use App\Models\TestOrder;
use App\Services\PdfGenerator;
use App\Services\TestOrderCreator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private function branchId(): int
    {
        return Auth::guard('branch')->id();
    }

    /** Branches may only touch orders they created themselves. */
    private function authorizeOrder(TestOrder $order): void
    {
        abort_unless($order->branch_id === $this->branchId(), 403);
    }

    public function index(Request $request, string $lab_slug)
    {
        $query = TestOrder::with('patient')->where('branch_id', $this->branchId());

        if ($request->filled('search')) {
            $query->whereHas('patient', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        return view('branch.orders.index', compact('orders'));
    }

    public function create(Request $request, string $lab_slug)
    {
        $patient = $request->filled('patient_id')
            ? Patient::where('branch_id', $this->branchId())->findOrFail($request->patient_id)
            : null;

        $patients = Patient::where('branch_id', $this->branchId())
                           ->orderBy('name')->get(['id', 'name', 'patient_code']);

        // The main lab's catalog — shared automatically via the tenant scope
        $tests = TestCatalog::where('is_active', true)->orderBy('category')->orderBy('name')->get();

        return view('branch.orders.create', compact('patients', 'patient', 'tests'));
    }

    public function store(Request $request, string $lab_slug)
    {
        $request->validate([
            'patient_id' => ['required',
                             Rule::exists('patients', 'id')->where('branch_id', $this->branchId())],
            'test_ids'   => 'required|array|min:1',
            'test_ids.*' => 'exists:test_catalogs,id',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $order = app(TestOrderCreator::class)->create([
            'patient_id' => $request->patient_id,
            'test_ids'   => $request->test_ids,
            'notes'      => $request->notes,
            'created_by' => null,
            'branch_id'  => $this->branchId(),
        ]);

        return redirect()->route('branch.orders.show', [$lab_slug, $order])
                         ->with('success', 'Test order created and invoice generated.');
    }

    public function show(string $lab_slug, TestOrder $order)
    {
        $this->authorizeOrder($order);
        $order->load(['patient', 'items.testCatalog', 'invoice']);
        return view('branch.orders.show', compact('order'));
    }

    public function markCollected(string $lab_slug, TestOrder $order)
    {
        $this->authorizeOrder($order);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be marked as sample collected.');
        }

        $order->update([
            'status'              => 'sample_collected',
            'sample_collected_at' => now(),
        ]);

        return back()->with('success', 'Sample marked as collected. The main lab will process it.');
    }

    public function downloadReport(Request $request, string $lab_slug, TestOrder $order)
    {
        $this->authorizeOrder($order);

        if (!in_array($order->status, ['results_ready', 'finalized'])) {
            return back()->with('error', 'Report is only available once results are ready.');
        }

        $pdf      = PdfGenerator::report($order);
        $filename = 'report-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->stream($filename, ['Attachment' => false]);
    }
}
