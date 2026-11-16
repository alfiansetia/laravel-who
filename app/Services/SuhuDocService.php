<?php

namespace App\Services;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;

/**
 * Generator dokumen pencatatan suhu gudang (.docx).
 *
 * Satu halaman per lokasi gudang. Setiap halaman memakai template
 * `public/master/suhu/suhu.docx` dengan variabel:
 * `${lokasi}`, `${bulan}`, `${tahun}`, dan baris `${tgl}` yang digandakan
 * sejumlah hari kerja (Senin–Jumat) pada bulan/tahun yang dipilih.
 */
class SuhuDocService
{
    private const TEMPLATE = 'master/suhu/suhu.docx';

    private const REFRIGERATOR_TEMPLATE = 'master/suhu/suhu_refrigrator.docx';

    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /**
     * Jumlah baris data per halaman. Template sudah membawa 1 baris kosong
     * di bawah baris ${tgl}, sehingga baris ${tgl} digandakan sebanyak
     * ROWS_PER_PAGE - 1 dan sisanya dikosongkan.
     */
    private const ROWS_PER_PAGE = 26;

    /**
     * @return array<int, string>
     */
    public static function gudangKeys(): array
    {
        return ['gudang_a', 'gudang_b'];
    }

    /**
     * @return array<int, string>
     */
    public static function lokasiFor(string $gudang): array
    {
        /** @var array<int, string> $lokasi */
        $lokasi = config('suhu.'.$gudang, []);

        return array_values($lokasi);
    }

    public static function monthName(int $bulan): string
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ][$bulan] ?? '';
    }

    /**
     * Daftar tanggal hari kerja (Senin–Jumat) dalam sebulan.
     *
     * @return array<int, string> Nomor tanggal tanpa nol depan, mis. ['1', '2', ...].
     */
    public static function workdays(int $bulan, int $tahun): array
    {
        $days = [];
        $total = Carbon::create($tahun, $bulan, 1)->daysInMonth;
        for ($day = 1; $day <= $total; $day++) {
            $date = Carbon::create($tahun, $bulan, $day);
            if (! $date->isWeekend()) {
                $days[] = (string) $day;
            }
        }

        return $days;
    }

    /**
     * Bangun file .docx final dan kembalikan path file sementara hasil.
     */
    public function generate(string $gudang, int $bulan, int $tahun, string $judul = 'PENCATATAN SUHU'): string
    {
        $lokasiList = self::lokasiFor($gudang);
        if ($lokasiList === []) {
            abort(422, 'Daftar lokasi gudang kosong.');
        }

        $template = public_path(self::TEMPLATE);
        if (! is_file($template)) {
            abort(500, 'Template suhu.docx tidak ditemukan.');
        }

        $workdays = self::workdays($bulan, $tahun);

        $pages = [];
        foreach ($lokasiList as $lokasi) {
            $pages[] = $this->fillPage(self::TEMPLATE, ['judul' => $judul, 'lokasi' => $lokasi], $workdays, $bulan, $tahun);
        }

        $output = tempnam(sys_get_temp_dir(), 'suhu_').'.docx';
        $this->mergePages($pages, $output);

        foreach ($pages as $page) {
            @unlink($page);
        }

        return $output;
    }

    public static function fileName(string $gudang, int $bulan, int $tahun, string $judul = 'PENCATATAN SUHU'): string
    {
        $gudangSlug = strtolower(str_replace('_', '-', $gudang));

        return sprintf('%s-%s-%s-%d.docx', Str::slug($judul), $gudangSlug, strtolower(self::monthName($bulan)), $tahun);
    }

    /**
     * Bangun file .docx pencatatan suhu refrigerator/freezer (satu halaman,
     * tanpa gudang) dan kembalikan path file sementara hasil.
     */
    public function generateRefrigerator(int $bulan, int $tahun): string
    {
        return $this->fillPage(self::REFRIGERATOR_TEMPLATE, [], self::workdays($bulan, $tahun), $bulan, $tahun);
    }

    public static function refrigeratorFileName(int $bulan, int $tahun): string
    {
        return sprintf('pencatatan-suhu-refrigrator-%s-%d.docx', strtolower(self::monthName($bulan)), $tahun);
    }

    /**
     * Isi satu halaman template: variabel teks + baris ${tgl} sejumlah hari
     * kerja (sisanya baris kosong hingga pas 1 halaman).
     *
     * @param  array<string, string>  $values
     * @param  array<int, string>  $workdays
     */
    private function fillPage(string $templateConst, array $values, array $workdays, int $bulan, int $tahun): string
    {
        $template = public_path($templateConst);
        if (! is_file($template)) {
            abort(500, 'Template '.$templateConst.' tidak ditemukan.');
        }

        $processor = new TemplateProcessor($template);
        $variables = $processor->getVariables();
        foreach ($values as $key => $value) {
            if (in_array($key, $variables, true)) {
                $processor->setValue($key, self::escape($value));
            }
        }
        $processor->setValue('bulan', self::escape(self::monthName($bulan)));
        $processor->setValue('tahun', (string) $tahun);
        $processor->cloneRowAndSetValues('tgl', array_map(
            fn (string $tgl): array => ['tgl' => $tgl],
            self::paddedWorkdays($workdays)
        ));

        $page = tempnam(sys_get_temp_dir(), 'suhu_page_').'.docx';
        $processor->saveAs($page);

        return $page;
    }

    /**
     * Tanggal kerja dilengkapi string kosong hingga pas 1 halaman penuh.
     *
     * @param  array<int, string>  $workdays
     * @return array<int, string>
     */
    private static function paddedWorkdays(array $workdays): array
    {
        return array_slice(array_pad($workdays, self::ROWS_PER_PAGE - 1, ''), 0, self::ROWS_PER_PAGE - 1);
    }

    /**
     * Escape nilai agar aman disisipkan ke XML dokumen Word.
     * TemplateProcessor menyisipkan nilai apa adanya.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    /**
     * Gabung beberapa file satu-halaman menjadi satu dokumen multi-halaman.
     * Setiap file lanjutan disambung dengan page break.
     *
     * @param  array<int, string>  $pages
     */
    private function mergePages(array $pages, string $output): void
    {
        copy($pages[0], $output);
        if (count($pages) === 1) {
            return;
        }

        $zip = new ZipArchive;
        $zip->open($output);
        $base = new DOMDocument('1.0', 'UTF-8');
        $baseXml = (string) $zip->getFromName('word/document.xml');
        if (! self::loadXml($base, $baseXml)) {
            $zip->close();
            abort(500, 'Gagal membaca dokumen hasil generate.');
        }
        $xpath = new DOMXPath($base);
        $xpath->registerNamespace('w', self::WORD_NS);

        $body = $xpath->query('/w:document/w:body')->item(0);
        $sectPr = $xpath->query('w:sectPr', $body)->item(0);

        foreach (array_slice($pages, 1) as $page) {
            $pageZip = new ZipArchive;
            $pageZip->open($page);
            $xml = (string) $pageZip->getFromName('word/document.xml');
            $pageZip->close();

            $doc = new DOMDocument('1.0', 'UTF-8');
            if (! self::loadXml($doc, $xml)) {
                $pageZip->close();
                $zip->close();
                abort(500, 'Gagal menggabung halaman dokumen.');
            }
            $docXpath = new DOMXPath($doc);
            $docXpath->registerNamespace('w', self::WORD_NS);
            $otherBody = $docXpath->query('/w:document/w:body')->item(0);

            $body->insertBefore($this->pageBreak($base), $sectPr);

            $children = [];
            foreach ($otherBody->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                if ($child->nodeName === 'w:sectPr') {
                    continue;
                }
                $body->insertBefore($base->importNode($child, true), $sectPr);
            }
        }

        $zip->addFromString('word/document.xml', (string) $base->saveXML());
        $zip->close();
    }

    private static function loadXml(DOMDocument $doc, string $xml): bool
    {
        $previous = libxml_use_internal_errors(true);
        $ok = $doc->loadXML($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $ok;
    }

    private function pageBreak(DOMDocument $doc): \DOMElement
    {
        $p = $doc->createElementNS(self::WORD_NS, 'w:p');
        $r = $doc->createElementNS(self::WORD_NS, 'w:r');
        $br = $doc->createElementNS(self::WORD_NS, 'w:br');
        $br->setAttribute('w:type', 'page');
        $r->appendChild($br);
        $p->appendChild($r);

        return $p;
    }
}
