<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\SendReportReadyJob;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\TestCatalog;
use App\Models\TestOrder;
use App\Models\TestOrderItem;
use App\Services\PdfGenerator;
use App\Services\TenantContext;
use App\Services\TestOrderCreator;
use Illuminate\Http\Request;

class TestOrderController extends Controller
{
    public function __construct(private TenantContext $context) {}

    public function index(Request $request, string $lab_slug)
    {
        $query = TestOrder::with('patient', 'createdBy', 'branch');

        if ($request->filled('search')) {
            $query->whereHas('patient', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        return view('tenant.orders.index', compact('orders'));
    }

    public function create(Request $request, string $lab_slug)
    {
        $appointment = $request->filled('appointment_id')
            ? Appointment::with('patient')->findOrFail($request->appointment_id)
            : null;

        $patient = $request->filled('patient_id')
            ? Patient::findOrFail($request->patient_id)
            : $appointment?->patient;

        $patients = Patient::orderBy('name')->get(['id', 'name', 'patient_code']);
        $tests    = TestCatalog::where('is_active', true)->orderBy('category')->orderBy('name')->get();

        return view('tenant.orders.create', compact('patients', 'patient', 'appointment', 'tests'));
    }

    public function store(Request $request, string $lab_slug)
    {
        $request->validate([
            'patient_id'     => 'required|exists:patients,id',
            'test_ids'       => 'required|array|min:1',
            'test_ids.*'     => 'exists:test_catalogs,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $order = app(TestOrderCreator::class)->create([
            'patient_id'     => $request->patient_id,
            'test_ids'       => $request->test_ids,
            'appointment_id' => $request->appointment_id,
            'notes'          => $request->notes,
            'created_by'     => auth()->id(),
        ]);

        return redirect()->route('tenant.orders.show', [$lab_slug, $order])
                         ->with('success', 'Test order created and invoice generated.');
    }

    public function show(string $lab_slug, TestOrder $order)
    {
        $order->load(['patient', 'appointment', 'items.testCatalog', 'items.enteredBy', 'invoice', 'createdBy', 'branch']);
        return view('tenant.orders.show', compact('order'));
    }

    public function edit(string $lab_slug, TestOrder $order)
    {
        if (!in_array($order->status, ['pending', 'sample_collected'])) {
            return redirect()->route('tenant.orders.show', [$lab_slug, $order])
                             ->with('error', 'Order cannot be edited in its current status.');
        }

        $order->load('items.testCatalog');
        $tests = TestCatalog::where('is_active', true)->orderBy('name')->get();
        return view('tenant.orders.edit', compact('order', 'tests'));
    }

    public function update(Request $request, string $lab_slug, TestOrder $order)
    {
        $request->validate(['notes' => 'nullable|string|max:1000']);
        $order->update(['notes' => $request->notes]);
        return redirect()->route('tenant.orders.show', [$lab_slug, $order])->with('success', 'Order updated.');
    }

    public function updateStatus(Request $request, string $lab_slug, TestOrder $order)
    {
        $transitions = [
            'pending'          => 'sample_collected',
            'sample_collected' => 'processing',
            'processing'       => 'results_ready',
            'results_ready'    => 'finalized',
        ];

        $newStatus = $transitions[$order->status] ?? null;

        if (!$newStatus) {
            return back()->with('error', 'No valid status transition from current status.');
        }

        $timestamps = [
            'sample_collected' => ['sample_collected_at' => now()],
            'results_ready'    => ['results_ready_at' => now()],
            'finalized'        => ['finalized_at' => now()],
        ];

        $order->update(array_merge(['status' => $newStatus], $timestamps[$newStatus] ?? []));

        if ($newStatus === 'results_ready') {
            $tenant = $this->context->get();
            SendReportReadyJob::dispatch($order, $tenant);
        }

        if ($request->wantsJson()) {
            $toastMap = [
                'sample_collected' => 'Sample collected ✓',
                'processing'       => 'Processing started',
                'results_ready'    => 'Results ready — patient notified',
                'finalized'        => 'Order finalized',
            ];
            return response()->json([
                'success'    => true,
                'new_status' => $newStatus,
                'toast'      => $toastMap[$newStatus] ?? 'Status updated',
            ]);
        }

        return back()->with('success', "Order status updated to: " . str_replace('_', ' ', $newStatus));
    }

    public function updateResult(Request $request, string $lab_slug, TestOrder $order, TestOrderItem $item)
    {
        $request->validate([
            'result_value' => 'nullable|string|max:2000',
            'remarks'      => 'nullable|string|max:1000',
            'result_file'  => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $data = [
            'result_value' => $request->result_value,
            'remarks'      => $request->remarks,
            'status'       => filled($request->result_value) ? 'completed' : 'pending',
            'entered_by'   => auth()->id(),
            'entered_at'   => now(),
        ];

        if ($request->hasFile('result_file')) {
            $tenant  = $this->context->get();
            $path    = $request->file('result_file')->store("tenants/{$tenant->id}/results", 'public');
            $data['result_file'] = $path;
        }

        $item->update($data);

        // Auto-advance to processing if all items have results
        if ($order->status === 'sample_collected' || $order->status === 'pending') {
            $order->update(['status' => 'processing']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'item_status'  => $item->fresh()->status,
                'order_status' => $order->fresh()->status,
            ]);
        }

        return back()->with('success', 'Result saved.');
    }

    public function destroy(string $lab_slug, TestOrder $order)
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be deleted.');
        }
        $order->delete();
        return redirect()->route('tenant.orders.index', $lab_slug)->with('success', 'Order deleted.');
    }

    public function downloadReport(Request $request, string $lab_slug, TestOrder $order)
    {
        if (!in_array($order->status, ['results_ready', 'finalized'])) {
            return back()->with('error', 'Report is only available once results are ready.');
        }

        // Header/footer can be omitted for printing on pre-printed letterhead.
        $withHeader = $request->query('header', '1') !== '0';
        $withFooter = $request->query('footer', '1') !== '0';

        $pdf      = PdfGenerator::report($order, $withHeader, $withFooter);
        $filename = 'report-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        // Stream inline so it opens in the browser's PDF viewer (with its own download/print).
        return $pdf->stream($filename, ['Attachment' => false]);
    }
}
