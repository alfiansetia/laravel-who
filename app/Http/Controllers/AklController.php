<?php

namespace App\Http\Controllers;

use App\Models\Akl;
use App\Services\AklFileStorage;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AklController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('AKL', route('akls.index'), false),
        ]);

        // Data dimuat via API (api.akls.index) ala alamat_baru.
        return view('akl.index', compact('bcms'));
    }

    public function create()
    {
        $bcms = collect([
            new Breadcrumb('AKL', route('akls.index'), true),
            new Breadcrumb('Upload Lampiran', route('akls.create'), false),
        ]);

        return view('akl.create', compact('bcms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reg_no'       => 'required|string|max:100',
            'reg_name'     => 'nullable|string|max:255',
            'vendor'       => 'nullable|string|max:255',
            'date_from'    => 'nullable|date',
            'date_expired' => 'nullable|date|after_or_equal:date_from',
            'file'         => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:20480',
        ]);

        $filename = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $this->buildFilename(
                $request->reg_no,
                $request->date_expired,
                strtolower($file->getClientOriginalExtension())
            );

            try {
                $ok = AklFileStorage::put($filename, file_get_contents($file->getRealPath()));
            } catch (\Throwable $e) {
                report($e);
                $ok = false;
            }

            if (! $ok) {
                return back()->withInput()
                    ->with('error', 'Upload gagal, file tidak tersimpan di S3/R2.');
            }
        }

        Akl::create([
            'reg_no'       => $request->reg_no,
            'reg_name'     => $request->reg_name,
            'vendor'       => $request->vendor,
            'date_from'    => $request->date_from,
            'date_expired' => $request->date_expired,
            'file'         => $filename,
        ]);

        return redirect()->route('akls.index')
            ->with('success', 'Data AKL berhasil disimpan.');
    }

    /**
     * Stream file dari S3 (PDF tampil inline, gambar tampil langsung).
     */
    public function show(Akl $akl)
    {
        if (! $akl->file) {
            abort(404, 'File tidak ada.');
        }

        $contents = AklFileStorage::get($akl->file);
        if ($contents === null) {
            abort(404, 'File tidak ditemukan di S3/R2.');
        }

        return response($contents, 200, [
            'Content-Type'        => AklFileStorage::mime($akl->file),
            'Content-Disposition' => 'inline; filename="'.$akl->file.'"',
        ]);
    }

    public function edit(Akl $akl)
    {
        $bcms = collect([
            new Breadcrumb('AKL', route('akls.index'), true),
            new Breadcrumb('Edit Lampiran', route('akls.edit', $akl->id), false),
        ]);

        return view('akl.edit', compact('bcms', 'akl'));
    }

    /**
     * Kelola item (product code ref + custom) per AKL.
     */
    public function items(Akl $akl)
    {
        $bcms = collect([
            new Breadcrumb('AKL', route('akls.index'), true),
            new Breadcrumb('Item '.$akl->reg_no, route('akls.items', $akl->id), false),
        ]);

        $akl->loadCount('items');

        return view('akl.items', compact('bcms', 'akl'));
    }

    public function update(Request $request, Akl $akl)
    {
        $request->validate([
            'reg_no'       => 'required|string|max:100',
            'reg_name'     => 'nullable|string|max:255',
            'vendor'       => 'nullable|string|max:255',
            'date_from'    => 'nullable|date',
            'date_expired' => 'nullable|date|after_or_equal:date_from',
            'file'         => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:20480',
        ]);

        $data = $request->only(['reg_no', 'reg_name', 'vendor', 'date_from', 'date_expired']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $this->buildFilename(
                $request->reg_no,
                $request->date_expired,
                strtolower($file->getClientOriginalExtension())
            );

            try {
                $ok = AklFileStorage::put($filename, file_get_contents($file->getRealPath()));
            } catch (\Throwable $e) {
                report($e);
                $ok = false;
            }

            if (! $ok) {
                return back()->withInput()
                    ->with('error', 'Upload gagal, file tidak tersimpan di S3/R2.');
            }

            if ($akl->file) {
                AklFileStorage::delete($akl->file);
            }
            $data['file'] = $filename;
        }

        $akl->update($data);

        return redirect()->route('akls.index')
            ->with('success', 'Data AKL berhasil diperbarui.');
    }

    public function destroy(Akl $akl)
    {
        $akl->delete(); // file S3 dihapus via model observer

        return redirect()->route('akls.index')
            ->with('success', 'Lampiran AKL berhasil dihapus.');
    }

    /**
     * Nama file lampiran: {reg_no}_{exp Ymd/NOEXP}_{4 random}.{ext}
     * cth: AKL_123_20280112_AB12.pdf
     */
    protected function buildFilename(?string $regNo, $dateExpired, string $extension): string
    {
        $safeReg = trim(preg_replace('/[^A-Za-z0-9]+/', '_', (string) $regNo), '_');
        if ($safeReg === '') {
            $safeReg = 'AKL';
        }

        try {
            $exp = $dateExpired
                ? \Carbon\Carbon::parse($dateExpired)->format('Ymd')
                : 'NOEXP';
        } catch (\Throwable $e) {
            $exp = 'NOEXP';
        }

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        // Jaga keunikan di storage (regenerasi 4 random bila tabrakan).
        for ($i = 0; $i < 5; $i++) {
            $filename = $safeReg.'_'.$exp.'_'.Str::upper(Str::random(4)).'.'.$extension;
            if (! AklFileStorage::exists($filename)) {
                return $filename;
            }
        }

        return $safeReg.'_'.$exp.'_'.Str::upper(Str::random(8)).'.'.$extension;
    }
}
