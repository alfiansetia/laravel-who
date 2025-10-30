<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sop;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SopController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['store']);
    }

    public function index()
    {
        $data = Sop::query()->with(['product', 'items'])->get()->unique('product_id')->values();
        return $this->sendResponse($data);
    }

    public function show($id)
    {
        $data = Sop::query()->with(['product', 'items'])->find($id);
        if (!$data) {
            return $this->sendNotFound();
        }
        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id'    => 'required|exists:products,id',
            'target'        => 'required|string|max:200',
            'items'         => 'array|min:1',
            'items.*.item'  => 'required_with:items|string|max:65535',
        ]);
        Sop::query()->filter($request->only(['product_id']))->delete();
        $sop = Sop::create([
            'product_id'    => $request->product_id,
            'target'        => $request->target,
        ]);
        if ($request->has('items')) {
            $sop->items()->createMany(
                collect($request->items)->map(fn($i) => ['item' => $i['item']])->toArray()
            );
        }
        return $this->sendResponse($sop, 'Success');
    }

    public function download($id)
    {
        $sop = Sop::with(['product', 'items'])->find($id);
        if (!$sop) {
            return $this->sendNotFound();
        }

        $templatePath = public_path("master/master_sop.xlsx");
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();
        $product_code  = 'Kode barang : ' . $sop->product->code ?? '';
        $product_name  = 'Nama barang : ' . $sop->product->name ?? '';
        $target  = 'Target : ' . $sop->target ?? '';

        // === HEADER INFO ===
        $sheet->setCellValue('B4', $product_code);
        $sheet->setCellValue('B5', $product_name);
        $sheet->setCellValue('B6', $target);

        // === ITEMS ===
        $startRow = 9;
        $row = $startRow;

        // Ambil style dari baris contoh (baris 9)
        $baseStyle = $sheet->getStyle("B{$startRow}:C{$startRow}");
        $baseRowHeight = $sheet->getRowDimension($startRow)->getRowHeight();

        foreach ($sop->items as $index => $item) {
            if ($row > $startRow) {
                // copy style ke baris baru
                $sheet->duplicateStyle($baseStyle, "B{$row}:C{$row}");
                $sheet->getRowDimension($row)->setRowHeight($baseRowHeight);
            }
            $sheet->setCellValue("B{$row}", $index + 1);
            $sheet->setCellValue("C{$row}", $item->item);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $row++;
        }

        // === SIMPAN FILE ===
        $file_name = preg_replace('/[^A-Za-z0-9_.\-+()]/', '-', $sop->product->code);
        $output = storage_path("app/{$file_name}.xlsx");
        $writer = new Xlsx($spreadsheet);
        $writer->save($output);

        return response()->download($output)->deleteFileAfterSend();
    }
}
