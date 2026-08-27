<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Services\ExcelService;
use Illuminate\Http\Request;
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
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
    }

    public function show($id)
    {
        $data = Pack::query()->with(['vendor', 'product', 'items'])->find($id);
        if (!$data) {
            return $this->sendNotFound();
        }
        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'          => 'required|string|max:200',
            'desc'          => 'nullable|string|max:200',
            'vendor_desc'   => 'nullable|string|max:200',
            'product_id'    => 'required|exists:products,id',
            'vendor_id'     => 'required|exists:vendors,id',
            'items'         => 'nullable|array',
            'items.*.item'  => 'required_with:items|string|max:65535',
            'items.*.qty'   => 'nullable|string|max:200',
        ]);
        $pack = Pack::create([
            'name'          => $request->name,
            'desc'          => $request->desc,
            'vendor_desc'   => $request->vendor_desc,
            'product_id'    => $request->product_id,
            'vendor_id'     => $request->vendor_id,
        ]);
        if (!empty($request->items)) {
            $items = collect($request->items)->map(function ($item) {
                return [
                    'item' => $item['item'] ?? null,
                    'qty'  => $item['qty'] ?? null,
                ];
            })->toArray();
            $pack->items()->createMany($items);
        }
        return $this->sendResponse($pack, 'Created!');
    }

    public function update(Request $request, $id)
    {
        $pack = Pack::find($id);
        if (!$pack) {
            return $this->sendNotFound();
        }
        $this->validate($request, [
            'name'          => 'required|string|max:200',
            'desc'          => 'nullable|string|max:200',
            'vendor_desc'   => 'nullable|string|max:200',
            'product_id'    => 'required|exists:products,id',
            'vendor_id'     => 'required|exists:vendors,id',
            'items'         => 'nullable|array',
            'items.*.item'  => 'required_with:items|string|max:65535',
            'items.*.qty'   => 'nullable|string|max:200',
        ]);
        $pack->update([
            'name'          => $request->name,
            'desc'          => $request->desc,
            'vendor_desc'   => $request->vendor_desc,
            'product_id'    => $request->product_id,
            'vendor_id'     => $request->vendor_id,
        ]);
        $pack->items()->delete();
        if (!empty($request->items)) {
            $items = collect($request->items)->map(function ($item) {
                return [
                    'item' => $item['item'] ?? null,
                    'qty'  => $item['qty'] ?? null,
                ];
            })->toArray();
            $pack->items()->createMany($items);
        }
        return $this->sendResponse($pack, 'Updated!');
    }

    public function destroy($id)
    {
        $pack = Pack::find($id);
        if (!$pack) {
            return $this->sendNotFound();
        }
        $pack->delete();
        return $this->sendResponse($pack, 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:packs,id',
        ]);
        $deleted = Pack::whereIn('id', $request->ids)->delete();
        return $this->sendResponse(['deleted_count' => $deleted], 'Pack deleted successfully.');
    }

    public function change(Request $request)
    {
        $this->validate($request, [
            'vendor_id' => 'required|exists:vendors,id',
            'ids'       => 'required|array',
            'ids.*'     => 'integer|exists:packs,id',
        ]);
        $updated = Pack::whereIn('id', $request->ids)
            ->update(['vendor_id' => $request->vendor_id]);

        return $this->sendResponse([
            'updated_count' => $updated
        ], 'Vendor changed successfully.');
    }

    public function download($id)
    {
        $pack = Pack::find($id);
        if (!$pack) {
            return $this->sendNotFound();
        }

        try {
            $path = $this->excelService->generatePack($pack);
            return response()->download($path)->deleteFileAfterSend();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
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
            return $this->sendError('Tidak ada data untuk diexport.');
        }

        try {
            $spreadsheet = new Spreadsheet();
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
            $dataRange = "A2:G" . ($row - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D6DCE4']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("A2:A" . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Column widths
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(16);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);

            // Auto-filter
            $sheet->setAutoFilter("A1:G" . ($row - 1));

            // Generate file
            $filename = 'Data-Packing-List-' . now()->format('Y-m-d_His') . '.xlsx';
            $outputPath = storage_path("app/temp/" . $filename);
            $outputDir = dirname($outputPath);
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($outputPath);

            return response()->download($outputPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend();
        } catch (\Exception $e) {
            return $this->sendError('Gagal generate Excel: ' . $e->getMessage());
        }
    }
}
