<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\Xls\Escher as XlsEscher;
use PhpOffice\PhpSpreadsheet\Shared\Escher;
use PhpOffice\PhpSpreadsheet\Shared\OLERead;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

/**
 * Menerapkan crop gambar .xls (Escher OPT) ke resource GD sebelum
 * dikonversi ke .xlsx.
 *
 * Latar: PhpSpreadsheet membaca gambar .xls sebagai MemoryDrawing tapi
 * mengabaikan crop (OPT 0x0100–0x0103, fraksi 16.16), dan writer Xlsx tidak
 * menulis a:srcRect. Hasilnya gambar konversi tampil utuh/tidak ter-crop.
 * Service ini memotong pikselnya di server sehingga .xlsx hasil konversi
 * sudah sesuai tampilan crop di Excel.
 */
class XlsDrawingCropService
{
    private const OPT_CROP_TOP = 0x0100;

    private const OPT_CROP_BOTTOM = 0x0101;

    private const OPT_CROP_LEFT = 0x0102;

    private const OPT_CROP_RIGHT = 0x0103;

    private const OPT_BLIP_INDEX = 0x0104;

    private const FIXED_POINT = 65536;

    /**
     * Pre-crop semua MemoryDrawing sesuai crop Escher. Best-effort:
     * tidak pernah melempar exception agar konversi tetap jalan.
     */
    public function applyCrop(string $xlsPath, Spreadsheet $spreadsheet): void
    {
        try {
            $drawingDataPerSheet = $this->extractDrawingData($xlsPath);

            foreach ($spreadsheet->getAllSheets() as $index => $worksheet) {
                $drawingData = $drawingDataPerSheet[$index] ?? '';
                if ($drawingData === '') {
                    continue;
                }

                $crops = $this->pictureCrops($drawingData);
                if (empty($crops)) {
                    continue;
                }

                $drawings = array_values(array_filter(
                    $worksheet->getDrawingCollection()->getArrayCopy(),
                    fn ($d): bool => $d instanceof MemoryDrawing
                ));

                // Terapkan hanya bila urutan 1:1 agar crop tidak salah gambar.
                if (count($drawings) !== count($crops)) {
                    continue;
                }

                foreach ($drawings as $i => $drawing) {
                    $this->cropDrawing($drawing, $crops[$i]);
                }
            }
        } catch (\Throwable $e) {
            // Abaikan: konversi tetap dilanjut tanpa crop.
        }
    }

    /**
     * @return array<int, array{float, float, float, float}> fraksi [l, t, r, b] per gambar
     */
    private function pictureCrops(string $drawingData): array
    {
        $escher = new Escher;
        $escher = (new XlsEscher($escher))->load($drawingData);

        if (! method_exists($escher, 'getDgContainer') || ! $escher->getDgContainer()) {
            return [];
        }

        $crops = [];
        foreach ($escher->getDgContainer()->getSpgrContainer()->getAllSpContainers() as $spContainer) {
            if ($spContainer->getNestingLevel() > 1 || ! $spContainer->getOPT(self::OPT_BLIP_INDEX)) {
                continue;
            }

            $crops[] = [
                $this->fraction($spContainer->getOPT(self::OPT_CROP_LEFT)),
                $this->fraction($spContainer->getOPT(self::OPT_CROP_TOP)),
                $this->fraction($spContainer->getOPT(self::OPT_CROP_RIGHT)),
                $this->fraction($spContainer->getOPT(self::OPT_CROP_BOTTOM)),
            ];
        }

        return $crops;
    }

    private function fraction(mixed $value): float
    {
        if ($value === null) {
            return 0.0;
        }

        $fraction = ((int) $value) / self::FIXED_POINT;

        return $fraction >= 0 && $fraction < 1 ? $fraction : 0.0;
    }

    /**
     * @param  array{float, float, float, float}  $crop  fraksi [l, t, r, b]
     */
    private function cropDrawing(MemoryDrawing $drawing, array $crop): void
    {
        [$left, $top, $right, $bottom] = $crop;

        if ($left + $right >= 1 || $top + $bottom >= 1) {
            return;
        }

        if ($left == 0.0 && $top == 0.0 && $right == 0.0 && $bottom == 0.0) {
            return;
        }

        $image = $drawing->getImageResource();
        if (! $image) {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= 0 || $height <= 0) {
            return;
        }

        $rect = [
            'x' => (int) round($width * $left),
            'y' => (int) round($height * $top),
            'width' => (int) round($width * (1 - $left - $right)),
            'height' => (int) round($height * (1 - $top - $bottom)),
        ];

        if ($rect['width'] < 1 || $rect['height'] < 1) {
            return;
        }

        $cropped = imagecrop($image, $rect);
        if ($cropped === false) {
            return;
        }

        if ($drawing->getMimeType() === MemoryDrawing::MIMETYPE_PNG) {
            imagealphablending($cropped, false);
            imagesavealpha($cropped, true);
        }

        $drawing->setImageResource($cropped);
        if (is_resource($image) || $image instanceof \GdImage) {
            imagedestroy($image);
        }
    }

    /**
     * Kumpulkan data MSODRAWING (0x00EC + CONTINUE kamuflase) per worksheet.
     * Replika minimal dari Xls::readMsoDrawing()/readContinue().
     *
     * @return array<int, string>
     */
    private function extractDrawingData(string $xlsPath): array
    {
        $ole = new OLERead;
        $ole->read($xlsPath);
        $data = $ole->getStream($ole->wrkbook);

        $sheets = [];
        $current = null;
        $pos = 0;
        $length = strlen($data);

        while ($pos + 4 <= $length) {
            $code = $this->uint16($data, $pos);
            $recordLength = $this->uint16($data, $pos + 2);
            $recordData = substr($data, $pos + 4, $recordLength);

            if ($code === 0x0809) { // BOF
                $dt = strlen($recordData) >= 4 ? $this->uint16($recordData, 2) : 0;
                $current = $dt === 0x0010 ? '' : null; // 0x0010 = worksheet
                $pos += 4 + $recordLength;
            } elseif ($code === 0x000A) { // EOF
                if ($current !== null) {
                    $sheets[] = $current;
                    $current = null;
                }
                $pos += 4 + $recordLength;
            } elseif ($code === 0x00EC && $current !== null) { // MSODRAWING
                $current .= $this->splice($data, $pos, $length);
                $pos = $this->spliceEnd($data, $pos, $length);
            } elseif ($code === 0x003C && $current !== null && $current !== '') { // CONTINUE
                if ($recordLength >= 4 && in_array($this->uint16($recordData, 2), [0xF003, 0xF004, 0xF00D], true)) {
                    $current .= $this->splice($data, $pos, $length);
                    $pos = $this->spliceEnd($data, $pos, $length);

                    continue;
                }
                $pos += 4 + $recordLength;
            } else {
                $pos += 4 + $recordLength;
            }
        }

        return $sheets;
    }

    /**
     * Gabung record + CONTINUE lanjutannya (replika getSplicedRecordData).
     */
    private function splice(string $data, int $pos, int $length): string
    {
        $buffer = '';
        $total = strlen($data);

        do {
            $recordLength = $this->uint16($data, $pos + 2);
            $buffer .= substr($data, $pos + 4, $recordLength);
            $pos += 4 + $recordLength;
        } while ($pos + 4 <= $total && $this->uint16($data, $pos) === 0x003C && $pos + 4 <= $length);

        return $buffer;
    }

    private function spliceEnd(string $data, int $pos, int $length): int
    {
        $total = strlen($data);

        do {
            $recordLength = $this->uint16($data, $pos + 2);
            $pos += 4 + $recordLength;
        } while ($pos + 4 <= $total && $this->uint16($data, $pos) === 0x003C && $pos + 4 <= $length);

        return $pos;
    }

    private function uint16(string $data, int $pos): int
    {
        return ord($data[$pos]) | (ord($data[$pos + 1]) << 8);
    }
}
