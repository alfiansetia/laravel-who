<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IzinEdar;
use Illuminate\Http\Request;

class IzinEdarController extends Controller
{
    /**
     * Display a paginated listing of izin edar with filters.
     * Optimized for 60k+ rows with server-side pagination.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 50), 200);
        $page = max((int) $request->get('page', 1), 1);

        $query = IzinEdar::query();

        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Search: nomor izin, merk, pendaftar, jenis_produk
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('nomor_izin_edar', 'like', "%{$keyword}%")
                  ->orWhere('merk', 'like', "%{$keyword}%")
                  ->orWhere('pendaftar', 'like', "%{$keyword}%")
                  ->orWhere('jenis_produk', 'like', "%{$keyword}%");
            });
        }

        $total = (clone $query)->count();

        $data = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get([
                'id', 'kategori', 'nomor_izin_edar', 'tgl_terbit', 'tgl_exp',
                'merk', 'jenis_produk', 'pendaftar', 'pabrik',
                'alamat_pendaftar', 'alamat_pabrik',
                'sub_kategori', 'kelompok_produk', 'tipe', 'kelas', 'kelas_resiko', 'pabrik2',
            ]);

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
    }

    /**
     * Display the specified izin edar.
     */
    public function show(IzinEdar $izinEdar)
    {
        return response()->json(['data' => $izinEdar]);
    }
}
