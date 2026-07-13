<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikiServices
{
    public static function getBaseUrl(): string
    {
        return rtrim(config('services.tiki_api_url'), '/');
    }

    public static function getApiKey(): string
    {
        return config('services.tiki_api_key');
    }

    /**
     * Track one or more TIKI connote numbers.
     *
     * @param  string|array  $connotes  Single resi string or array of resi strings
     * @return array|null
     */
    public static function track(string|array $connotes): ?array
    {
        $connoteParam = is_array($connotes) ? implode(',', $connotes) : $connotes;

        $url = self::getBaseUrl() . '/api/track';

        try {
            $response = Http::withHeaders([
                'X-API-Key' => self::getApiKey(),
                'Accept'    => 'application/json',
            ])->timeout(30)->get($url, [
                'resi' => $connoteParam,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            // Log::error('TIKI Track API error', [
            //     'status' => $response->status(),
            //     'body'   => $response->body(),
            //     'resi'   => $connoteParam,
            // ]);

            return null;
        } catch (\Exception $e) {
            // Log::error('TIKI Track API exception', [
            //     'message' => $e->getMessage(),
            //     'resi'   => $connoteParam,
            // ]);

            return null;
        }
    }
}
