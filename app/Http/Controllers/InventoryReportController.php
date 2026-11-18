<?php

namespace App\Http\Controllers;

use App\Services\InventoryReportServices;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InventoryReportController extends Controller
{
    public function index(Request $request, InventoryReportServices $reports)
    {
        $excludeGaStock = $request->boolean('exclude_ga_stock', true);

        if ($this->isApi($request)) {
            $data = $reports->getAll($excludeGaStock);

            return $this->sendResponse($data);
        }

        return Inertia::render('InventoryReport/Index', [
            'title' => 'Inventory Report',
            'filters' => [
                'search' => $request->input('search', ''),
                'exclude_ga_stock' => $excludeGaStock,
            ],
        ]);
    }

    public function locations(Request $request, int $id, InventoryReportServices $reports)
    {
        $excludeGaStock = $request->boolean('exclude_ga_stock', true);
        $data = $reports->getLocations($id, $excludeGaStock);

        return $this->sendResponse($data);
    }

    public function export(Request $request, InventoryReportServices $reports)
    {
        set_time_limit(300);

        $excludeGaStock = $request->boolean('exclude_ga_stock', true);
        $matrix = $reports->getMatrix($excludeGaStock);
        // Sheet mentah mengikuti filter yang sama agar konsisten dengan rapihan.
        $spreadsheet = InventoryReportServices::buildWorkbook($matrix, null, $matrix);

        $filename = 'Inventory Report '.date('d F Y').'.xlsx';
        $outputPath = storage_path('app/temp/'.$filename);
        if (! file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0775, true);
        }

        (new Xlsx($spreadsheet))->save($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend();
    }
}
