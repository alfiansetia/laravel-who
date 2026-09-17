<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Akl;
use Illuminate\Http\Request;

class AklController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'destroy_batch']);
    }

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 25), 200);
        $page = max((int) $request->input('page', 1), 1);

        $query = Akl::query();

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('reg_no', 'like', "%{$keyword}%")
                    ->orWhere('reg_name', 'like', "%{$keyword}%")
                    ->orWhere('vendor', 'like', "%{$keyword}%");
            });
        }

        $total = (clone $query)->count();

        // reg_no boleh sama di banyak baris (perpanjangan), tampilkan per dokumen.
        $data = $query->orderByDesc('date_expired')
            ->orderByDesc('id')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get([
                'id',
                'reg_no',
                'reg_name',
                'vendor',
                'date_from',
                'date_expired',
                'file',
                'created_at',
            ]);

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
    }

    public function destroy($id)
    {
        $akl = Akl::find($id);
        if (! $akl) {
            return $this->sendNotFound();
        }
        $akl->delete(); // file S3 dihapus via model observer

        return $this->sendResponse($akl, 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:akls,id',
        ]);

        // Hapus per model (bukan query-delete) agar observer ikut hapus file S3.
        $images = Akl::whereIn('id', $request->ids)->get();
        foreach ($images as $akl) {
            $akl->delete();
        }

        return $this->sendResponse([
            'deleted_count' => $images->count(),
        ], 'AKL deleted successfully.');
    }
}
