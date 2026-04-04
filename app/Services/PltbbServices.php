<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class PltbbServices
{

    public static function get()
    {
        $url = config('services.spreadsheet_url');
        if (empty($url)) {
            throw new Exception('URL spreadsheet tidak ditemukan');
        }
        $tempPath = storage_path('app/temp_' . uniqid() . '.xlsx');
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0'
        ])->get($url);

        if (!$response->ok()) {
            throw new Exception('Gagal mengambil file dari Google Sheets');
        }
        $body = $response->body();
        if (str_contains($body, '<html') || str_contains($body, '<!DOCTYPE')) {
            throw new Exception('Gagal: Google meminta login atau file tidak dipublish sebagai publik');
        }
        file_put_contents($tempPath, $body);
        $data = Excel::toCollection([], $tempPath);
        if ($data->isEmpty() || $data->first()->isEmpty()) {
            throw new Exception('Data tidak ditemukan');
        }
        $sheet = $data->first();
        $header = $sheet->get(1);
        $header = $header ? $header->map(fn($h) => $h ? trim($h) : null) : collect();
        $result = $sheet->slice(3)->map(function ($row) use ($header) {
            return $row->take($header->count())->toArray();
        })->reject(function ($row) {
            if (empty(array_filter($row))) return true;
            return empty($row[3]) && empty($row[4]);
        })->values();
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
        return $result;
    }
}
