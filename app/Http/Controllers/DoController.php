<?php

namespace App\Http\Controllers;

use App\Services\DoMonitorService;
use App\Services\DoServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class DoController extends Controller
{
    public function index(Request $request, DoServices $do)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 10), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');
            $note_search = (string) ($request->input('note_search') ?? '');
            $product_search = (string) ($request->input('product_search') ?? '');
            $date_from = (string) ($request->input('date_from') ?? '');
            $date_to = (string) ($request->input('date_to') ?? '');
            $gudang = max((int) $request->input('gudang', 2), 1);

            if ($request->input('export') === 'csv') {
                return $this->exportCsv($do, $gudang, $search, $note_search, $product_search, $date_from, $date_to);
            }

            $response = $do->withGudang($gudang)->getAll($search, $perPage, $offset, $note_search, $product_search, $date_from, $date_to);
            $total = Arr::get($response, 'length', 0);
            $data = Arr::get($response, 'records', []);

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
            ]);
        }

        return Inertia::render('Do/Index', [
            'title' => 'DO',
            'filters' => $request->only(['search', 'page', 'note_search', 'product_search', 'date_from', 'date_to', 'gudang']),
        ]);
    }

    public function detail(int $id, DoServices $do)
    {
        $id = intval($id);
        $response = $do->detail($id);

        return $this->sendResponse($response);
    }

    public function monitor(DoMonitorService $monitor)
    {
        $response = $monitor->getAll();

        return $this->sendResponse($response ?? []);
    }

    /**
     * Export CSV seluruh data sesuai filter aktif (bukan cuma halaman tampil).
     * Dipakai tombol "CSV Semua" di halaman DO.
     */
    protected function exportCsv(DoServices $do, int $gudang, string $search, string $note_search, string $product_search, string $date_from, string $date_to)
    {
        $limit = 2000;
        $response = $do->withGudang($gudang)->getAll($search, $limit, 0, $note_search, $product_search, $date_from, $date_to);
        $records = Arr::get($response, 'records', []);

        $columns = ['no_do', 'tgl_konfirmasi_so', 'fdate', 'partner', 'so', 'status', 'note'];
        $lines = [implode(';', $columns)];
        foreach ($records as $row) {
            $partner = Arr::get($row, 'partner_id');
            $lines[] = implode(';', [
                $this->csvCell(Arr::get($row, 'name')),
                $this->csvCell($this->csvDate(Arr::get($row, 'x_studio_field_0rSR5', Arr::get($row, 'confirmation_date_so')))),
                $this->csvCell($this->csvDate(Arr::get($row, 'force_date'))),
                $this->csvCell(is_array($partner) ? ($partner[1] ?? '') : $partner),
                $this->csvCell(Arr::get($row, 'origin')),
                $this->csvCell(Arr::get($row, 'state')),
                $this->csvCell(Arr::get($row, 'note_to_wh')),
            ]);
        }

        $filename = 'do-'.now()->format('Ymd-His').'.csv';

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function csvCell($value): string
    {
        $s = (string) ($value === false || $value === null ? '' : $value);

        return '"'.str_replace('"', '""', $s).'"';
    }

    /**
     * Konversi datetime UTC Odoo ke WIB untuk CSV.
     * Return '' bila kosong agar sel CSV bersih.
     */
    protected function csvDate($value): string
    {
        if (empty($value) || $value === false || $value === 'False') {
            return '';
        }
        try {
            $formatted = odoo_datetime($value, 'Y-m-d H:i:s');
        } catch (\Throwable) {
            return (string) $value;
        }

        return $formatted === 'False' ? '' : $formatted;
    }

    public function print(Request $request, $id, DoServices $do)
    {
        $with_lot = $request->with_lot ?? false;
        $data = $do->detail($id);
        if (! isset($data['id'])) {
            return redirect()->route('do.index')->with('error', 'Data Tidak Ditemukan');
        }

        return view('do.print', compact('data', 'with_lot'));
    }
}
