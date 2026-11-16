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

    private const HAMA_TEMPLATE = 'master/suhu/hama.docx';

    private const PEMBERSIHAN_TEMPLATE = 'master/suhu/pembersihan_gudang.docx';

    private const PALET_TEMPLATE = 'master/suhu/pembersihan_palet.docx';

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
     * Bangun file .docx pengendalian hama: satu halaman per lokasi gudang,
     * tanpa baris tanggal.
     */
    public function generateHama(string $gudang, int $bulan, int $tahun): string
    {
        $lokasiList = self::lokasiFor($gudang);
        if ($lokasiList === []) {
            abort(422, 'Daftar lokasi gudang kosong.');
        }

        $pages = [];
        foreach ($lokasiList as $lokasi) {
            $pages[] = $this->fillPage(self::HAMA_TEMPLATE, ['lokasi' => $lokasi], [], $bulan, $tahun);
        }

        $output = tempnam(sys_get_temp_dir(), 'hama_').'.docx';
        $this->mergePages($pages, $output);

        foreach ($pages as $page) {
            @unlink($page);
        }

        return $output;
    }

    public static function hamaFileName(string $gudang, int $bulan, int $tahun): string
    {
        $gudangSlug = strtolower(str_replace('_', '-', $gudang));

        return sprintf('pengendalian-hama-%s-%s-%d.docx', $gudangSlug, strtolower(self::monthName($bulan)), $tahun);
    }

    /**
     * Bangun file .docx pembersihan gudang: satu halaman per lokasi gudang.
     * Kolom tanggal 1-31 sudah fix di template; sel tanggal yang jatuh di
     * akhir pekan (atau di luar jumlah hari bulan itu) dicap 'X' berdasarkan
     * posisi kolom, tanpa variabel tambahan.
     */
    public function generatePembersihan(string $gudang, int $bulan, int $tahun): string
    {
        $lokasiList = self::lokasiFor($gudang);
        if ($lokasiList === []) {
            abort(422, 'Daftar lokasi gudang kosong.');
        }

        $pages = [];
        foreach ($lokasiList as $lokasi) {
            $pages[] = $this->fillPembersihanPage($lokasi, $bulan, $tahun);
        }

        $output = tempnam(sys_get_temp_dir(), 'bersih_').'.docx';
        $this->mergePages($pages, $output);

        foreach ($pages as $page) {
            @unlink($page);
        }

        return $output;
    }

    public static function pembersihanFileName(string $gudang, int $bulan, int $tahun): string
    {
        $gudangSlug = strtolower(str_replace('_', '-', $gudang));

        return sprintf('pembersihan-gudang-%s-%s-%d.docx', $gudangSlug, strtolower(self::monthName($bulan)), $tahun);
    }

    /**
     * Pilihan gudang palet (fix, jarang berubah).
     *
     * @return array<string, string>
     */
    public static function paletGudang(): array
    {
        return [
            'a' => 'Gudang B05 (LT.1)',
            'b' => 'Gudang C26',
            'c' => 'Gudang C01',
        ];
    }

    /**
     * Bangun file .docx perawatan dan pembersihan palet (3 halaman, satu per
     * gudang). $tgl diisi dd/mm/yyyy, nama tiap gudang dari form.
     *
     * @param  array<string, string>  $namaGudang
     */
    public function generatePalet(array $namaGudang, Carbon $tanggal): string
    {
        $template = public_path(self::PALET_TEMPLATE);
        if (! is_file($template)) {
            abort(500, 'Template pembersihan_palet.docx tidak ditemukan.');
        }

        $defaults = self::paletGudang();
        $processor = new TemplateProcessor($template);
        $processor->setValue('tgl', $tanggal->format('d/m/Y'));
        $processor->setValue('bulan', self::escape(self::monthName($tanggal->month)));
        $processor->setValue('tahun', (string) $tanggal->year);
        foreach (['a', 'b', 'c'] as $key) {
            $nama = trim($namaGudang[$key] ?? '');
            $processor->setValue('gudang_'.$key, self::escape($nama !== '' ? $nama : $defaults[$key]));
        }

        $page = tempnam(sys_get_temp_dir(), 'palet_').'.docx';
        $processor->saveAs($page);

        return $page;
    }

    public static function paletFileName(Carbon $tanggal): string
    {
        return sprintf('pembersihan-palet-%s.docx', $tanggal->format('d-m-Y'));
    }

    private function fillPembersihanPage(string $lokasi, int $bulan, int $tahun): string
    {
        $page = $this->fillPage(self::PEMBERSIHAN_TEMPLATE, ['lokasi' => $lokasi], [], $bulan, $tahun);
        $this->markOffDayCells($page, $bulan, $tahun);

        return $page;
    }

    /**
     * Cap '-' pada sel tanggal yang bukan hari kerja (Sabtu/Minggu atau
     * tanggal di luar jumlah hari bulan itu). Layout template dianggap:
     * baris 0 header, baris 1 kepala tanggal, baris 2..(N-2) baris tugas,
     * baris terakhir tanda tangan — dengan 3 kolom label di depan lalu
     * kolom tanggal 1-31 (baris tanda tangan menggabung 3 kolom label jadi
     * tanggal mulai di sel indeks 1). Tambah baris tugas di template otomatis
     * ikut dicap.
     */
    private function markOffDayCells(string $docxPath, int $bulan, int $tahun): void
    {
        $daysInMonth = Carbon::create($tahun, $bulan, 1)->daysInMonth;
        $offDays = [];
        for ($day = 1; $day <= 31; $day++) {
            if ($day > $daysInMonth || Carbon::create($tahun, $bulan, $day)->isWeekend()) {
                $offDays[] = $day;
            }
        }
        if ($offDays === []) {
            return;
        }

        $zip = new ZipArchive;
        $zip->open($docxPath);
        $xml = (string) $zip->getFromName('word/document.xml');

        $doc = new DOMDocument('1.0', 'UTF-8');
        if (! self::loadXml($doc, $xml)) {
            $zip->close();
            abort(500, 'Gagal membaca dokumen hasil generate.');
        }
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('w', self::WORD_NS);

        $rows = $xpath->query('//w:tbl[1]//w:tr');
        $totalRows = $rows->length;
        if ($totalRows < 4) {
            $zip->close();
            abort(500, 'Layout template pembersihan berubah.');
        }
        $headerCells = $xpath->query('./w:tc', $rows->item(1));
        $lastTaskRow = $totalRows - 2;
        for ($r = 2; $r <= $lastTaskRow; $r++) {
            $cells = $xpath->query('./w:tc', $rows->item($r));
            foreach ($offDays as $day) {
                $cellIndex = 3 + $day - 1;
                $source = $headerCells->item($cellIndex);
                $target = $cells->item($cellIndex);
                if (! $source instanceof \DOMElement || ! $target instanceof \DOMElement) {
                    continue;
                }
                $this->stampCell($xpath, $source, $target, '-');
            }
        }

        $signatureRow = $rows->item($totalRows - 1);
        $signatureLabel = '';
        foreach ($xpath->query('./w:tc[1]//w:t', $signatureRow) as $text) {
            $signatureLabel .= $text->nodeValue;
        }
        if (str_contains(strtolower($signatureLabel), 'tanda tangan')) {
            $signatureCells = $xpath->query('./w:tc', $signatureRow);
            foreach ($offDays as $day) {
                $source = $headerCells->item(3 + $day - 1);
                $target = $signatureCells->item($day);
                if (! $source instanceof \DOMElement || ! $target instanceof \DOMElement) {
                    continue;
                }
                $this->stampCell($xpath, $source, $target, '-');
            }
        }

        $zip->addFromString('word/document.xml', (string) $doc->saveXML());
        $zip->close();
    }

    /**
     * Salin format paragraf sel kepala tanggal lalu isi dengan tanda.
     */
    private function stampCell(DOMXPath $xpath, \DOMElement $source, \DOMElement $target, string $mark): void
    {
        foreach (iterator_to_array($xpath->query('./w:p', $target)) as $old) {
            $target->removeChild($old);
        }

        $sourceParagraph = $xpath->query('./w:p[1]', $source)->item(0);
        if (! $sourceParagraph instanceof \DOMElement) {
            return;
        }
        $paragraph = $sourceParagraph->cloneNode(true);
        $texts = $xpath->query('.//w:t', $paragraph);
        foreach ($texts as $i => $text) {
            if ($i === 0) {
                $text->nodeValue = $mark;
            } else {
                $text->parentNode?->removeChild($text);
            }
        }
        $target->appendChild($paragraph);
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
        if (in_array('bulan', $variables, true)) {
            $processor->setValue('bulan', self::escape(self::monthName($bulan)));
        }
        if (in_array('tahun', $variables, true)) {
            $processor->setValue('tahun', (string) $tahun);
        }
        if (in_array('tgl', $variables, true) && $workdays !== []) {
            $processor->cloneRowAndSetValues('tgl', array_map(
                fn (string $tgl): array => ['tgl' => $tgl],
                self::paddedWorkdays($workdays)
            ));
        }

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
