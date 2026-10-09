<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sop;
use App\Models\SopItem;
use App\Services\ExcelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SopController extends Controller
{
    protected $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
        $this->middleware('env_auth')->only(['store']);
    }

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 25), 200);
            $page = max((int) $request->input('page', 1), 1);

            // Subquery: get latest SOP id per product_id
            $query = Sop::query()
                ->whereIn('id', function ($sub) {
                    $sub->selectRaw('MAX(id)')->from('sops')->groupBy('product_id');
                })
                ->with(['product']);

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('target', 'like', "%{$keyword}%")
                        ->orWhereHas('product', function ($q2) use ($keyword) {
                            $q2->where('code', 'like', "%{$keyword}%")
                                ->orWhere('name', 'like', "%{$keyword}%");
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

        return Inertia::render('Sop/Index', [
            'title' => 'SOP QC',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function create()
    {
        $products = Product::query()->select('id', 'code', 'name')->orderBy('code')->get();
        $sop_items = SopItem::query()
            ->select('item')
            ->distinct()
            ->get();

        return Inertia::render('Sop/Create', [
            'title' => 'Manage SOP QC',
            'products' => $products,
            'sopItems' => $sop_items->pluck('item'),
        ]);
    }

    public function show(Request $request, $sop): JsonResponse|RedirectResponse
    {
        $data = Sop::query()->with(['product', 'items'])->find($sop);
        if (! $data) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->route('sops.index')
                ->with('error', 'Data SOP tidak ditemukan.');
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($data);
        }

        return redirect()->route('sops.index');
    }

    public function edit(Sop $sop)
    {
        return redirect()->route('sops.index');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'target' => 'required|string|max:200',
            'items' => 'array|min:1',
            'items.*.item' => 'required_with:items|string|max:65535',
        ]);
        $sop = Sop::updateOrCreate(
            ['product_id' => $request->product_id],
            ['target' => $request->target]
        );

        $sop->items()->delete();

        if ($request->has('items')) {
            $sop->items()->createMany(
                collect($request->items)->map(fn ($i) => ['item' => $i['item']])->toArray()
            );
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($sop, 'Success');
        }

        return redirect()->route('sops.index')
            ->with('success', 'Data SOP berhasil disimpan.');
    }

    public function print(Sop $sop)
    {
        $sop->load(['product', 'items']);

        return view('sop.print', compact('sop'));
    }

    public function download(Request $request, $sop): JsonResponse|BinaryFileResponse|RedirectResponse
    {
        $sopModel = Sop::find($sop);
        if (! $sopModel) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->route('sops.index')
                ->with('error', 'Data SOP tidak ditemukan.');
        }

        $product = $sopModel->product;
        if (! $product) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->route('sops.index')
                ->with('error', 'Data SOP tidak ditemukan.');
        }

        try {
            $path = $this->excelService->generateSop($product);

            return response()->download($path)->deleteFileAfterSend();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return $this->sendError($e->getMessage());
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function export(Request $request): JsonResponse|BinaryFileResponse|RedirectResponse
    {
        $query = Sop::query()
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')->from('sops')->groupBy('product_id');
            })
            ->with(['product']);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('target', 'like', "%{$keyword}%")
                    ->orWhereHas('product', function ($q2) use ($keyword) {
                        $q2->where('code', 'like', "%{$keyword}%")
                            ->orWhere('name', 'like', "%{$keyword}%");
                    });
            });
        }

        $sops = $query->orderBy('id', 'desc')->get();

        if ($sops->isEmpty()) {
            if ($request->wantsJson()) {
                return $this->sendError('Tidak ada data untuk diexport.');
            }

            return back()->with('error', 'Tidak ada data untuk diexport.');
        }

        try {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data SOP QC');

            // === Header Row ===
            $headers = ['No', 'Kode Product', 'Nama Product', 'Target'];
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
            $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(24);

            // === Data Rows ===
            $row = 2;
            foreach ($sops as $index => $sop) {
                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue("B{$row}", $sop->product->code ?? '-');
                $sheet->setCellValue("C{$row}", $sop->product->name ?? '-');
                $sheet->setCellValue("D{$row}", $sop->target ?? '-');

                // Alternate row color
                if ($index % 2 === 0) {
                    $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F7FB']],
                    ]);
                }

                $row++;
            }

            // Style data rows
            $dataRange = 'A2:D'.($row - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D6DCE4']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle('A2:A'.($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Column widths
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(16);
            $sheet->getColumnDimension('C')->setWidth(35);
            $sheet->getColumnDimension('D')->setWidth(25);

            // Auto-filter
            $sheet->setAutoFilter('A1:D'.($row - 1));

            // Generate file
            $filename = 'Data-SOP-QC-'.now()->format('Y-m-d_His').'.xlsx';
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

            return back()->with('error', 'Gagal generate Excel: '.$e->getMessage());
        }
    }
}
