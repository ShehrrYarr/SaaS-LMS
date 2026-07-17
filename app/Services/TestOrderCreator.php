<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TestCatalog;
use App\Models\TestOrder;
use App\Models\TestOrderItem;
use Illuminate\Support\Facades\DB;

/**
 * Creates a test order (expanding panels and nested panels into snapshotted
 * items) plus its auto-generated invoice. Shared by the main-lab panel and
 * the branch panel so the expansion/billing logic stays in one place.
 */
class TestOrderCreator
{
    /**
     * @param array $data keys: patient_id, test_ids (ordered array of catalog
     *                    ids), appointment_id?, notes?, created_by?, branch_id?
     */
    public function create(array $data): TestOrder
    {
        return DB::transaction(function () use ($data) {
            $order = TestOrder::create([
                'patient_id'     => $data['patient_id'],
                'appointment_id' => $data['appointment_id'] ?? null,
                'created_by'     => $data['created_by'] ?? null,
                'branch_id'      => $data['branch_id'] ?? null,
                'status'         => 'pending',
                'notes'          => $data['notes'] ?? null,
            ]);

            // Keep the order the user selected things in
            $selected = TestCatalog::with(['panelItems.test.panelItems.test'])
                ->whereIn('id', $data['test_ids'])
                ->get()
                ->sortBy(fn ($t) => array_search($t->id, $data['test_ids']))
                ->values();

            $invoiceLines = []; // one line per selected catalog (panel = bundled price)
            $sort         = 0;

            foreach ($selected as $catalog) {
                if ($catalog->is_panel) {
                    // Expand the panel into its child tests, snapshotting the section layout
                    $currentHeader = null;

                    foreach ($catalog->panelItems as $pi) {
                        if ($pi->type === 'header') {
                            $currentHeader = $pi->header_label;
                            continue;
                        }

                        if ($pi->type === 'panel') {
                            // Nested panel: expand its tests as a sub-section named after it.
                            // Its internal headers are flattened away; the parent's running
                            // header ($currentHeader) resumes after this block.
                            $nested = $pi->test;
                            if (!$nested) {
                                continue;
                            }
                            foreach ($nested->panelItems as $npi) {
                                if ($npi->type !== 'test' || !$npi->test) {
                                    continue;
                                }
                                TestOrderItem::create([
                                    'test_order_id'   => $order->id,
                                    'test_catalog_id' => $npi->test->id,
                                    'panel_id'        => $catalog->id,
                                    'panel_name'      => $catalog->name,
                                    'section_header'  => $nested->name,
                                    'sort_order'      => $sort++,
                                    'price'           => 0, // priced at the parent panel level
                                    'result_type'     => $npi->test->result_type ?? 'numeric',
                                    'status'          => 'pending',
                                ]);
                            }
                            continue;
                        }

                        $child = $pi->test;
                        if (!$child) {
                            continue;
                        }
                        TestOrderItem::create([
                            'test_order_id'   => $order->id,
                            'test_catalog_id' => $child->id,
                            'panel_id'        => $catalog->id,
                            'panel_name'      => $catalog->name,
                            'section_header'  => $currentHeader,
                            'sort_order'      => $sort++,
                            'price'           => 0, // priced at the panel level, not per child
                            'result_type'     => $child->result_type ?? 'numeric',
                            'status'          => 'pending',
                        ]);
                    }

                    // One bundled invoice line for the whole panel
                    $invoiceLines[] = [
                        'description' => $catalog->name,
                        'unit_price'  => (float) $catalog->price,
                    ];
                } else {
                    // Standalone single test
                    TestOrderItem::create([
                        'test_order_id'   => $order->id,
                        'test_catalog_id' => $catalog->id,
                        'panel_id'        => null,
                        'panel_name'      => null,
                        'section_header'  => null,
                        'sort_order'      => $sort++,
                        'price'           => (float) $catalog->price,
                        'result_type'     => $catalog->result_type ?? 'numeric',
                        'status'          => 'pending',
                    ]);

                    $invoiceLines[] = [
                        'description' => $catalog->name,
                        'unit_price'  => (float) $catalog->price,
                    ];
                }
            }

            $total = array_sum(array_column($invoiceLines, 'unit_price'));

            // Auto-generate invoice
            $invNumber = 'INV-' . now()->format('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'patient_id'     => $data['patient_id'],
                'test_order_id'  => $order->id,
                'invoice_number' => $invNumber,
                'subtotal'       => $total,
                'discount'       => 0,
                'total'          => $total,
                'amount_paid'    => 0,
                'status'         => 'unpaid',
            ]);

            foreach ($invoiceLines as $line) {
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $line['description'],
                    'quantity'    => 1,
                    'unit_price'  => $line['unit_price'],
                    'total'       => $line['unit_price'],
                ]);
            }

            return $order;
        });
    }
}
