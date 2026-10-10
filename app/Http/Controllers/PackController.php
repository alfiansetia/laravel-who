<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\PackItem;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PackController extends Controller
{
    protected $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
        $this->middleware('env_auth')->only(['update', 'change', 'destroy', 'destroy_batch']);
    }

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 25), 200);
            $page = max((int) $request->input('page', 1), 1);

            $query = Pack::query()->with(['vendor', 'product']);

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('desc', 'like', "%{$keyword}%")
                        ->orWhere('vendor_desc', 'like', "%{$keyword}%")
                        ->orWhereHas('product', function ($q2) use ($keyword) {
                            $q2->where('code', 'like', "%{$keyword}%")
                                ->orWhere('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('vendor', function ($q2) use ($keyword) {
                            $q2->where('name', 'like', "%{$keyword}%");
                        });
                });
            }

            $total = (clone $query)->count();

            $data = $query->orderBy('id', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        }

        $vendors = Vendor::query()->select('id', 'name')->orderBy('name')->limit(50)->get();

        return Inertia::render('Pack/Index', [
            'title' => 'Packing List',
            'vendors' => $vendors,
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function create()
    {
        // 50 pertama untuk opsi awal; sisanya via async search di form.
        $products = Product::query()->select('id', 'code', 'name')->orderBy('code')->limit(50)->get();
        $vendors = Vendor::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Pack/Create', [
            'title' => 'Create Packing List',
            'products' => $products,
            'vendors' => $vendors,
        ]);
    }

    public function edit(Pack $pack)
    {
        $data = $pack->load(['product', 'vendor']);
        // 50 pertama untuk opsi awal; sisanya via async search di form.
        $products = Product::query()->select('id', 'code', 'name')->orderBy('code')->limit(50)->get();
        $vendors = Vendor::query()->select('id', 'name')->orderBy('name')->get();

        // Opsi dropdown awal dibatasi 50; pastikan product milik pack
        // tetap terkirim agar terseleksi otomatis di form edit.
        if ($pack->product_id && ! $products->contains('id', $pack->product_id)) {
            $extraProduct = Product::query()->select('id', 'code', 'name')->find($pack->product_id);
            if ($extraProduct) {
                $products->push($extraProduct);
            }
        }

        return Inertia::render('Pack/Edit', [
            'title' => 'Edit Packing List',
            'pack' => $data,
            'products' => $products,
            'vendors' => $vendors,
        ]);
    }

    public function show(Request $request, $id)
    {
        $data = Pack::query()->with(['vendor', 'product', 'items.children'])->find($id);
        if (! $data) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->route('packs.index');
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($data);
        }

        return redirect()->route('packs.index');
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->itemRules());
        $pack = Pack::create([
            'name' => $request->name,
            'desc' => $request->desc,
            'vendor_desc' => $request->vendor_desc,
            'product_id' => $request->product_id,
            'vendor_id' => $request->vendor_id,
        ]);
        if (! empty($request->items)) {
            // Backward compat: flat legacy rows [{item,qty}] without children key
            // are treated as top-level items.
            $this->saveItems($pack, $request->items);
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($pack->load('items.children'), 'Created!');
        }

        return redirect()->route('packs.index')->with('success', 'Created!');
    }

    public function update(Request $request, $id)
    {
        $pack = Pack::find($id);
        if (! $pack) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }
            abort(404);
        }
        $this->validate($request, $this->itemRules());
        $pack->update([
            'name' => $request->name,
            'desc' => $request->desc,
            'vendor_desc' => $request->vendor_desc,
            'product_id' => $request->product_id,
            'vendor_id' => $request->vendor_id,
        ]);
        $this->saveItems($pack, $request->items ?? []);

        if ($request->wantsJson()) {
            return $this->sendResponse($pack->load('items.children'), 'Updated!');
        }

        return redirect()->route('packs.index')->with('success', 'Updated!');
    }

    public function destroy(Request $request, $id)
    {
        $pack = Pack::find($id);
        if (! $pack) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }
            abort(404);
        }
        $pack->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($pack, 'Deleted!');
        }

        return redirect()->route('packs.index')->with('success', 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:packs,id',
        ]);
        $deleted = Pack::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse(['deleted_count' => $deleted], 'Pack deleted successfully.');
        }

        return redirect()->route('packs.index')->with('success', 'Pack deleted successfully.');
    }

    public function change(Request $request)
    {
        $this->validate($request, [
            'vendor_id' => 'required|exists:vendors,id',
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:packs,id',
        ]);
        $updated = Pack::whereIn('id', $request->ids)
            ->update(['vendor_id' => $request->vendor_id]);

        if ($request->wantsJson()) {
            return $this->sendResponse([
                'updated_count' => $updated,
            ], 'Vendor changed successfully.');
        }

        return redirect()->route('packs.index')->with('success', 'Vendor changed successfully.');
    }

    public function download(Request $request, $id)
    {
        $pack = Pack::find($id);
        if (! $pack) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }
            abort(404);
        }

        try {
            $path = $this->excelService->generatePack($pack);

            return response()->download($path)->deleteFileAfterSend();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return $this->sendError($e->getMessage());
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $query = Pack::query()->with(['vendor', 'product']);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('desc', 'like', "%{$keyword}%")
                    ->orWhere('vendor_desc', 'like', "%{$keyword}%")
                    ->orWhereHas('product', function ($q2) use ($keyword) {
                        $q2->where('code', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('vendor', function ($q2) use ($keyword) {
                        $q2->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        $packs = $query->orderBy('id', 'desc')->get();

        if ($packs->isEmpty()) {
            if ($request->wantsJson()) {
                return $this->sendError('Tidak ada data untuk diexport.');
            }

            return redirect()->back()->with('error', 'Tidak ada data untuk diexport.');
        }

        try {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Packing List');

            // === Header Row ===
            $headers = ['No', 'Kode Product', 'Nama Product', 'PL Name', 'PL Desc', 'Vendor', 'Vendor Desc'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue("{$col}1", $header);
                $col++;
            }

            // Style header
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '2F5496']]],
            ];
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(24);

            // === Data Rows ===
            $row = 2;
            foreach ($packs as $index => $pack) {
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("B{$row}", $pack->product->code ?? '-');
                $sheet->setCellValue("C{$row}", $pack->product->name ?? '-');
                $sheet->setCellValue("D{$row}", $pack->name ?? '-');
                $sheet->setCellValue("E{$row}", $pack->desc ?? '-');
                $sheet->setCellValue("F{$row}", $pack->vendor->name ?? '-');
                $sheet->setCellValue("G{$row}", $pack->vendor_desc ?? '-');

                // Alternate row color
                if ($index % 2 === 0) {
                    $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F7FB']],
                    ]);
                }

                $row++;
            }

            // Style data rows
            $dataRange = 'A2:G'.($row - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D6DCE4']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle('A2:A'.($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Column widths
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(16);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);

            // Auto-filter
            $sheet->setAutoFilter('A1:G'.($row - 1));

            // Generate file
            $filename = 'Data-Packing-List-'.now()->format('Y-m-d_His').'.xlsx';
            $outputPath = storage_path('app/temp/'.$filename);
            $outputDir = dirname($outputPath);
            if (! file_exists($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($outputPath);

            return response()->download($outputPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return $this->sendError('Gagal generate Excel: '.$e->getMessage());
            }

            return redirect()->back()->with('error', 'Gagal generate Excel: '.$e->getMessage());
        }
    }

    public function print(Pack $pack)
    {
        $pack->load(['product.sop.items', 'vendor', 'items.children']);

        return view('pack.print', compact('pack'));
    }

    public function printCombined(Pack $pack)
    {
        $pack->load(['product.sop.items', 'vendor', 'items.children', 'product.pltbb']);
        $sop = $pack->product->sop;

        return view('pack.print_combined', compact('pack', 'sop'));
    }

    public function packItemIndex(Request $request)
    {
        $packId = $request->input('pack_id');

        if ($packId) {
            $pack = Pack::find($packId);
            if (! $pack) {
                return $this->sendResponse([]);
            }
            $rows = PackItem::flattenedFor($pack);

            $data = collect($rows)->map(function ($r) {
                /** @var PackItem $m */
                $m = $r['model'];

                return [
                    'id' => $m->id,
                    'pack_id' => $m->pack_id,
                    'parent_id' => $m->parent_id,
                    'item' => $m->item,
                    'qty' => $m->qty,
                    'is_group' => (bool) $m->is_group,
                    'show_number' => (bool) $m->show_number,
                    'sort_order' => $m->sort_order,
                    'level' => $r['level'],
                    'display_no' => $r['display_no'],
                ];
            })->values();

            return $this->sendResponse($data);
        }

        $data = PackItem::with(['pack'])->filter($request->only(['pack_id']))->get();

        return $this->sendResponse($data);
    }

    protected function itemRules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'desc' => 'nullable|string|max:200',
            'vendor_desc' => 'nullable|string|max:200',
            'product_id' => 'required|exists:products,id',
            'vendor_id' => 'required|exists:vendors,id',
            'items' => 'nullable|array',
            'items.*.item' => 'required_with:items|string|max:65535',
            'items.*.qty' => 'nullable|string|max:200',
            'items.*.is_group' => 'nullable|boolean',
            'items.*.show_number' => 'nullable|boolean',
            'items.*.children' => 'nullable|array|max:100',
            'items.*.children.*.item' => 'nullable|string|max:65535',
            'items.*.children.*.qty' => 'nullable|string|max:200',
            'items.*.children.*.show_number' => 'nullable|boolean',
        ];
    }

    protected function saveItems(Pack $pack, array $items): void
    {
        $pack->items()->delete();
        foreach (array_values($items) as $i => $top) {
            if (empty($top['item']) && empty($top['children'])) {
                continue;
            }
            // Legacy flat row that is actually a child (level=1 without parent context)
            // is sent nested by the UI, so here every top-level entry is a top.
            $topModel = $pack->items()->create([
                'item' => $top['item'] ?? null,
                'qty' => ! empty($top['is_group']) ? null : ($top['qty'] ?? null),
                'parent_id' => null,
                'sort_order' => $i,
                'is_group' => (bool) ($top['is_group'] ?? false),
                'show_number' => array_key_exists('show_number', $top) ? (bool) $top['show_number'] : true,
            ]);
            $children = $top['children'] ?? [];
            foreach (array_values($children) as $j => $child) {
                if (empty($child['item'])) {
                    continue;
                }
                $pack->items()->create([
                    'item' => $child['item'] ?? null,
                    'qty' => $child['qty'] ?? null,
                    'parent_id' => $topModel->id,
                    'sort_order' => $j,
                    'is_group' => false,
                    'show_number' => array_key_exists('show_number', $child) ? (bool) $child['show_number'] : true,
                ]);
            }
        }
    }
}
