<?php

namespace Tests\Feature;

use App\Services\Import\CekImportService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class CekImportTest extends TestCase
{
    public function test_build_fills_headers_and_items_on_both_sheets(): void
    {
        $items = [
            ['code' => 'KODE1', 'name' => 'Barang Satu', 'qty' => 10],
            ['code' => 'KODE2', 'name' => 'Barang Dua', 'qty' => 25],
        ];

        $path = CekImportService::build('PO01617', 'CENT/IN/02573', 'OTH Others', 'Jl. Contoh No. 1', $items);

        try {
            $this->assertFileExists($path);

            $wb = IOFactory::load($path);
            $import = $wb->getSheet(0);
            $this->assertSame('PO : PO01617', $import->getCell('A5')->getValue());
            $this->assertSame('RI : CENT/IN/02573', $import->getCell('E5')->getValue());
            $this->assertSame(': OTH Others', $import->getCell('C9')->getValue());
            $this->assertSame(': Jl. Contoh No. 1', $import->getCell('C10')->getValue());

            $this->assertSame(1, $import->getCell('A15')->getValue());
            $this->assertSame('KODE1', $import->getCell('B15')->getValue());
            $this->assertSame('Barang Satu', $import->getCell('C15')->getValue());
            $this->assertEquals(10, $import->getCell('D15')->getValue());
            $this->assertSame(2, $import->getCell('A16')->getValue());
            $this->assertSame('KODE2', $import->getCell('B16')->getValue());

            $karton = null;
            foreach ($wb->getAllSheets() as $sheet) {
                if (trim($sheet->getTitle()) === 'cek by karton') {
                    $karton = $sheet;
                    break;
                }
            }
            $this->assertNotNull($karton);
            $this->assertSame('PO : PO01617', $karton->getCell('A5')->getValue());
            $this->assertSame('RI : CENT/IN/02573', $karton->getCell('E5')->getValue());
            $this->assertSame(': OTH Others', $karton->getCell('C9')->getValue());
            $this->assertSame(': Jl. Contoh No. 1', $karton->getCell('C10')->getValue());
            // Sheet karton tidak diisi item.
            $this->assertNull($karton->getCell('A15')->getValue());

            $wb->disconnectWorksheets();
        } finally {
            if (isset($path) && file_exists($path)) {
                @unlink($path);
            }
        }
    }

    public function test_build_falls_back_to_dash_and_expands_rows_beyond_capacity(): void
    {
        $items = [];
        for ($i = 1; $i <= 30; $i++) {
            $items[] = ['code' => "K{$i}", 'name' => "Barang {$i}", 'qty' => $i];
        }

        $path = CekImportService::build('PO1', '', '', '', $items);

        try {
            $wb = IOFactory::load($path);
            $import = $wb->getSheet(0);
            $this->assertSame('RI : ', $import->getCell('E5')->getValue());
            $this->assertSame(': -', $import->getCell('C9')->getValue());
            $this->assertSame(': -', $import->getCell('C10')->getValue());
            // Baris ke-30 jatuh di baris 44 (15 + 29), footer ikut terdorong.
            $this->assertSame(30, $import->getCell('A44')->getValue());
            $this->assertSame('K30', $import->getCell('B44')->getValue());
            // Footer (CHECKER / KA WAREHOUSE) terdorong 6 baris ke bawah, tidak tertimpa item.
            $this->assertSame('CHECKER', trim((string) $import->getCell('B51')->getValue()));
            $this->assertSame('KA WAREHOUSE', trim((string) $import->getCell('F51')->getValue()));
            // Nama file mengikuti format "PO - (Vendor)".
            $this->assertSame('PO1 - (-).xlsx', basename($path));
            $wb->disconnectWorksheets();
        } finally {
            if (isset($path) && file_exists($path)) {
                @unlink($path);
            }
        }
    }

    public function test_filename_format(): void
    {
        $this->assertSame('PO01616 - (PT Tripatria).xlsx', CekImportService::filename('PO01616', 'PT Tripatria'));
        $this->assertSame('PO01616 - (OTH PT Tripatria).xlsx', CekImportService::filename('PO01616', 'OTH PT Tripatria'));
        // Karakter ilegal Windows disanitasi, spasi berlebih dirapikan.
        $this->assertSame('PO_1 - (PT A_B).xlsx', CekImportService::filename('PO/1', 'PT A/B'));
        $this->assertSame('- - (-).xlsx', CekImportService::filename('', ''));
    }

    public function test_split_product_and_compose_address(): void
    {
        $this->assertSame(['ABC', 'Nama Barang'], CekImportService::splitProduct([1, '[ABC] Nama Barang']));
        $this->assertSame('-', CekImportService::composeAddress(null));
        $this->assertSame('-', CekImportService::composeAddress(['street' => false, 'street2' => '', 'city' => null]));
        $this->assertSame(
            'Jl. A, Blok B, Jakarta, 12345',
            CekImportService::composeAddress(['street' => 'Jl. A', 'street2' => 'Blok B', 'city' => 'Jakarta', 'zip' => '12345'])
        );
    }
}
