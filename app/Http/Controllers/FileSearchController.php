<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class FileSearchController extends Controller
{
    public function index()
    {
        $title = 'File Search Manager';
        $jsonPath = storage_path('app/public/file-index.json');
        $lastUpdated = File::exists($jsonPath) ? date('Y-m-d H:i:s', File::lastModified($jsonPath)) : null;

        return view('file-search.index', compact('title', 'lastUpdated'));
    }

    public function getData()
    {
        $jsonPath = storage_path('app/public/file-index.json');

        if (File::exists($jsonPath)) {
            return response(File::get($jsonPath))->header('Content-Type', 'application/json');
        }

        return response()->json([]);
    }

    public function downloadScript()
    {
        $path = base_path('deployment/generate-index.bat');

        if (empty($path) || !File::exists($path)) {
            return back()->with('error', 'Script tidak ditemukan di folder deployment.');
        }

        return response()->download($path, 'generate-index.bat');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'json_file' => 'required|file'
        ]);

        if ($request->hasFile('json_file')) {
            $file = $request->file('json_file');
            $jsonPath = storage_path('app/public/file-index.json');

            // Ensure directory exists
            if (!File::isDirectory(storage_path('app/public'))) {
                File::makeDirectory(storage_path('app/public'), 0755, true);
            }

            // Move the file manually
            if ($file->move(storage_path('app/public'), 'file-index.json')) {
                $lastUpdated = date('Y-m-d H:i:s', File::lastModified($jsonPath));

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'File index berhasil diperbarui!',
                        'last_updated' => $lastUpdated
                    ]);
                }

                return back()->with('success', 'File index berhasil diperbarui!');
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Gagal mengunggah file.'], 400);
        }

        return back()->with('error', 'Gagal mengunggah file.');
    }
}
