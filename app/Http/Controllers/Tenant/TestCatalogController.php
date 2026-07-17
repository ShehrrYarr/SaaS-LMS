<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\PanelItem;
use App\Models\TestCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TestCatalogController extends Controller
{
    public function index(Request $request, string $lab_slug)
    {
        $query = TestCatalog::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('type')) {
            $query->where('is_panel', $request->type === 'panel');
        }

        $tests      = $query->with('panelItems.test.panelItems')->orderBy('category')->orderBy('name')->paginate(25)->withQueryString();
        $categories = TestCatalog::select('category')->distinct()->whereNotNull('category')->pluck('category');

        return view('tenant.tests.index', compact('tests', 'categories'));
    }

    public function create(string $lab_slug)
    {
        $categories = TestCatalog::select('category')->distinct()->whereNotNull('category')->pluck('category');
        $tests      = TestCatalog::where('is_panel', false)->where('is_active', true)->orderBy('name')
                        ->get(['id', 'name', 'code', 'category', 'price', 'unit', 'result_type']);
        $panels     = $this->nestablePanels();
        return view('tenant.tests.create', compact('categories', 'tests', 'panels'));
    }

    /**
     * Panels eligible to be nested inside another panel: active panels that
     * don't themselves contain panels (one level of nesting only).
     */
    private function nestablePanels(?int $excludeId = null)
    {
        return TestCatalog::where('is_panel', true)->where('is_active', true)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereDoesntHave('panelItems', fn ($q) => $q->where('type', 'panel'))
            ->withCount(['panelItems as tests_count' => fn ($q) => $q->where('type', 'test')])
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'category', 'price']);
    }

    public function store(Request $request, string $lab_slug)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:191',
            'code'         => 'nullable|string|max:50',
            'category'     => 'nullable|string|max:100',
            'price'        => 'required|numeric|min:0',
            'unit'         => 'nullable|string|max:50',
            'normal_range' => 'nullable|string|max:191',
            'result_type'  => 'nullable|in:numeric,text',
            'description'  => 'nullable|string|max:1000',
            'is_panel'     => 'boolean',
            'panel_layout' => 'nullable|string', // JSON: ordered headers + tests
        ]);

        $data['is_panel']    = $request->boolean('is_panel');
        $data['is_active']   = true;
        $data['result_type'] = $data['result_type'] ?? 'numeric';

        return DB::transaction(function () use ($data, $request, $lab_slug) {
            $test = TestCatalog::create($data);

            if ($data['is_panel']) {
                $this->syncPanelLayout($test, $request->input('panel_layout'));
            }

            return redirect()->route('tenant.tests.index', $lab_slug)
                             ->with('success', "Test \"{$test->name}\" added to catalog.");
        });
    }

    public function edit(string $lab_slug, TestCatalog $test)
    {
        $categories     = TestCatalog::select('category')->distinct()->whereNotNull('category')->pluck('category');
        $availableTests = TestCatalog::where('is_panel', false)->where('is_active', true)->where('id', '!=', $test->id)
                            ->orderBy('name')->get(['id', 'name', 'code', 'category', 'price', 'unit', 'result_type']);

        $availablePanels   = $this->nestablePanels($test->id);
        $isNestedElsewhere = $test->is_panel && $test->isNestedAnywhere();

        // Existing layout for the builder
        $layout = $test->panelItems->map(fn ($pi) => match ($pi->type) {
            'header' => ['type' => 'header', 'label' => $pi->header_label],
            'panel'  => ['type' => 'panel', 'id' => $pi->test_id],
            default  => ['type' => 'test', 'id' => $pi->test_id],
        })->values();

        return view('tenant.tests.edit', compact('test', 'categories', 'availableTests', 'availablePanels', 'isNestedElsewhere', 'layout'));
    }

    public function update(Request $request, string $lab_slug, TestCatalog $test)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:191',
            'code'         => 'nullable|string|max:50',
            'category'     => 'nullable|string|max:100',
            'price'        => 'required|numeric|min:0',
            'unit'         => 'nullable|string|max:50',
            'normal_range' => 'nullable|string|max:191',
            'result_type'  => 'nullable|in:numeric,text',
            'description'  => 'nullable|string|max:1000',
            'is_active'    => 'boolean',
            'panel_layout' => 'nullable|string',
        ]);

        $data['is_active']   = $request->boolean('is_active');
        $data['result_type'] = $data['result_type'] ?? 'numeric';

        return DB::transaction(function () use ($data, $request, $test, $lab_slug) {
            $test->update($data);

            if ($test->is_panel) {
                $this->syncPanelLayout($test, $request->input('panel_layout'));
            }

            return redirect()->route('tenant.tests.index', $lab_slug)
                             ->with('success', 'Test updated.');
        });
    }

    /**
     * Rebuild a panel's ordered items (headers + tests + nested panels) from the submitted JSON layout.
     */
    private function syncPanelLayout(TestCatalog $panel, ?string $layoutJson): void
    {
        $layout = json_decode($layoutJson ?? '[]', true);
        if (!is_array($layout)) {
            $layout = [];
        }

        $validTestIds = TestCatalog::where('is_panel', false)->pluck('id')->flip();

        // Validate nested-panel entries before touching existing items, so a
        // violation surfaces as a validation error instead of a partial save.
        $panelIds = collect($layout)
            ->filter(fn ($row) => ($row['type'] ?? null) === 'panel')
            ->map(fn ($row) => (int) ($row['id'] ?? 0))
            ->unique()
            ->values();

        if ($panelIds->isNotEmpty()) {
            if ($panel->exists && $panel->isNestedAnywhere()) {
                throw ValidationException::withMessages([
                    'panel_layout' => 'This panel is used inside another panel, so it cannot contain panels itself.',
                ]);
            }

            // Eligible: a panel other than this one that doesn't itself contain panels (one level only).
            $validNestedPanelIds = TestCatalog::where('is_panel', true)
                ->where('id', '!=', $panel->id)
                ->whereDoesntHave('panelItems', fn ($q) => $q->where('type', 'panel'))
                ->pluck('id')->flip();

            foreach ($panelIds as $panelId) {
                if (!$validNestedPanelIds->has($panelId)) {
                    $name = TestCatalog::where('id', $panelId)->value('name') ?? "#{$panelId}";
                    throw ValidationException::withMessages([
                        'panel_layout' => "\"{$name}\" cannot be nested here — it contains panels, was removed, or is not a panel.",
                    ]);
                }
            }
        }

        $panel->panelItems()->delete();

        $sort       = 0;
        $usedPanels = [];
        foreach ($layout as $row) {
            $type = $row['type'] ?? null;

            if ($type === 'header') {
                $label = trim((string) ($row['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                PanelItem::create([
                    'panel_id'     => $panel->id,
                    'type'         => 'header',
                    'header_label' => $label,
                    'sort_order'   => $sort++,
                ]);
            } elseif ($type === 'test') {
                $testId = (int) ($row['id'] ?? 0);
                if (!$validTestIds->has($testId)) {
                    continue;
                }
                PanelItem::create([
                    'panel_id'   => $panel->id,
                    'type'       => 'test',
                    'test_id'    => $testId,
                    'sort_order' => $sort++,
                ]);
            } elseif ($type === 'panel') {
                $panelId = (int) ($row['id'] ?? 0);
                if (isset($usedPanels[$panelId])) {
                    continue; // dedupe
                }
                $usedPanels[$panelId] = true;
                PanelItem::create([
                    'panel_id'   => $panel->id,
                    'type'       => 'panel',
                    'test_id'    => $panelId,
                    'sort_order' => $sort++,
                ]);
            }
        }
    }

    public function destroy(string $lab_slug, TestCatalog $test)
    {
        $name = $test->name;
        $test->delete();
        return redirect()->route('tenant.tests.index', $lab_slug)
                         ->with('success', "\"$name\" removed from catalog.");
    }
}
