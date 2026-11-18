<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Scraper halaman L&D Program (website Odoo) menjadi daftar card:
 * judul + link materi + link post-test.
 *
 * URL asli tiap tombol tersimpan di param `?redirect=` milik
 * `/web/login?redirect=...`, sehingga perlu dikupas. Domain diambil
 * dari env (services.odoo.domain_web), path halaman konstan.
 */
class LndServices
{
    private const PATH = '/l-d-program-main';

    private const CACHE_KEY = 'lnd_programs';

    public function getPrograms(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(12), function (): array {
            $base = rtrim((string) config('services.odoo.domain_web'), '/');

            $response = Http::timeout(15)->get($base.self::PATH);
            $response->throw();

            return self::parsePrograms($response->body(), $base);
        });
    }

    /**
     * @return array<int, array{title: string, materi_url: string, post_test_url: string}>
     */
    public static function parsePrograms(string $html, string $baseUrl = ''): array
    {
        $doc = new DOMDocument;
        $prev = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        $xpath = new DOMXPath($doc);

        $programs = [];
        $sections = $xpath->query("//section[contains(@class, 's_features')]");

        foreach ($sections as $section) {
            $h1 = $xpath->query('preceding-sibling::h1[1]', $section)->item(0);
            if (! $h1) {
                continue;
            }

            $title = trim(preg_replace('/^\x{00A0}+|\x{00A0}+$/u', '', $h1->textContent ?? ''));
            $title = preg_replace('/\s+/u', ' ', $title);

            $links = $xpath->query('.//a[@href]', $section);
            if ($links->count() < 2 || $title === '') {
                continue;
            }

            $programs[] = [
                'title' => $title,
                'materi_url' => self::resolveUrl($links->item(0)->getAttribute('href'), $baseUrl),
                'post_test_url' => self::resolveUrl($links->item(1)->getAttribute('href'), $baseUrl),
            ];
        }

        return $programs;
    }

    /**
     * Kupas `/web/login?redirect=URL_ASLI`; URL relatif ditempel ke base.
     */
    private static function resolveUrl(string $href, string $baseUrl): string
    {
        $href = trim($href);
        $query = parse_url($href, PHP_URL_QUERY) ?: '';
        parse_str($query, $params);

        $url = trim((string) ($params['redirect'] ?? $href));

        if ($url !== '' && ! preg_match('#^https?://#i', $url)) {
            $url = rtrim($baseUrl, '/').'/'.ltrim($url, '/');
        }

        return $url;
    }
}
