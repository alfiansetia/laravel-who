<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PackController extends Controller
{
    public function index()
    {
        $data = Pack::query()->with(['vendor', 'product', 'items'])->get();
        return $this->sendResponse($data);
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
            'product_id'    => 'required|exists:products,id',
            'vendor_id'     => 'required|exists:vendors,id',
            'items'         => 'nullable|array',
            'items.*.item'  => 'required_with:items|string|max:65535',
            'items.*.qty'   => 'nullable|string|max:200',
        ]);
        $pack = Pack::create([
            'name'          => $request->name,
            'desc'          => $request->desc,
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
            'product_id'    => 'required|exists:products,id',
            'vendor_id'     => 'required|exists:vendors,id',
            'items'         => 'nullable|array',
            'items.*.item'  => 'required_with:items|string|max:65535',
            'items.*.qty'   => 'nullable|string|max:200',
        ]);
        $pack->update([
            'name'          => $request->name,
            'desc'          => $request->desc,
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
        $pack = Pack::with(['vendor', 'product', 'items'])->find($id);
        if (!$pack) {
            return $this->sendNotFound();
        }

        $templatePath = public_path("master/master_pack.xlsx");
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $vendor  = "Pabrikan : " . ($pack->vendor->name ?? '');
        $product  = "Produk : " . $pack->product->code . " " . $pack->product->name;
        if (!empty($pack->desc)) {
            $product .= " ({$pack->desc})";
        }

        // === HEADER INFO ===
        $sheet->setCellValue('B4', $vendor);
        $sheet->setCellValue('B5', $product);

        // === ITEMS ===
        $startRow = 8;
        $row = $startRow;

        // Ambil style dari baris contoh (baris 8)
        $baseStyle = $sheet->getStyle("B{$startRow}:E{$startRow}");
        $baseRowHeight = $sheet->getRowDimension($startRow)->getRowHeight();

        foreach ($pack->items as $index => $item) {
            if ($row > $startRow) {
                // copy style ke baris baru
                $sheet->duplicateStyle($baseStyle, "B{$row}:E{$row}");
                $sheet->getRowDimension($row)->setRowHeight($baseRowHeight);
            }
            $sheet->setCellValue("B{$row}", $index + 1);
            $sheet->setCellValue("C{$row}", $item->item);
            $sheet->setCellValue("D{$row}", $item->qty);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // === TAMBAHKAN TEXT "FORM/WH/009/20.2" DI KOLOM E ===
        $cdakb = config('cdakb.pack');
        $sheet->setCellValue("E{$row}", $cdakb);
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // === SIMPAN FILE ===
        $file_name = preg_replace('/[^A-Za-z0-9_.\-+()]/', '-', $pack->product->code);
        $output = storage_path("app/{$file_name}.xlsx");
        $writer = new Xlsx($spreadsheet);
        $writer->save($output);

        return response()->download($output)->deleteFileAfterSend();
    }
}
