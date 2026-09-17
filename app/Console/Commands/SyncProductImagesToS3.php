<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use App\Services\ProductImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncProductImagesToS3 extends Command
{
    protected $signature = 'product-images:sync-s3
                            {--dry-run : Tampilkan rencana tanpa upload}
                            {--force : Upload ulang walau file sudah ada di S3}
                            {--delete-local : Hapus file local setelah terverifikasi ada di S3}
                            {--limit= : Batasi jumlah record DB yang diproses}';

    protected $description = 'Sync file product_images dari disk local (public) ke S3/R2';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $deleteLocal = (bool) $this->option('delete-local');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $query = ProductImage::query()->orderBy('id');
        if ($limit) {
            $query->limit($limit);
        }

        $total = (clone $query)->count();
        $this->info("Total record: {$total}".($dryRun ? ' [DRY-RUN]' : ''));

        $stats = [
            'uploaded' => 0,
            'skipped_exists' => 0,
            'missing_local' => 0,
            'failed' => 0,
            'deleted_local' => 0,
        ];
        $missing = [];

        foreach ($query->cursor() as $img) {
            $filename = $img->name;
            if (! $filename) {
                $stats['missing_local']++;
                continue;
            }

            $key = ProductImageStorage::key($filename);
            $existsS3 = ProductImageStorage::existsOnS3($filename);
            $existsLocal = ProductImageStorage::existsOnLocal($filename);

            if ($existsS3 && ! $force) {
                $stats['skipped_exists']++;

                if ($deleteLocal && $existsLocal && ! $dryRun) {
                    Storage::disk('public')->delete($key);
                    $stats['deleted_local']++;
                }

                continue;
            }

            if (! $existsLocal) {
                $stats['missing_local']++;
                $missing[] = "#{$img->id} {$filename}";
                continue;
            }

            if ($dryRun) {
                $this->line("  would upload: {$filename}");
                $stats['uploaded']++;
                continue;
            }

            try {
                $contents = Storage::disk('public')->get($key);
                $ok = ProductImageStorage::put($filename, $contents);

                if ($ok && ProductImageStorage::existsOnS3($filename)) {
                    $stats['uploaded']++;
                    $this->line("  uploaded: {$filename}");

                    if ($deleteLocal) {
                        Storage::disk('public')->delete($key);
                        $stats['deleted_local']++;
                    }
                } else {
                    $stats['failed']++;
                    $this->error("  failed: {$filename}");
                }
            } catch (\Throwable $e) {
                $stats['failed']++;
                $this->error("  error {$filename}: {$e->getMessage()}");
            }
        }

        // Fase 2: file orphan di local yang tidak ada di DB (opsional info)
        $this->newLine();
        $this->info('Ringkasan:');
        foreach ($stats as $k => $v) {
            $this->line("  {$k}: {$v}");
        }

        if (! empty($missing) && count($missing) <= 20) {
            $this->warn('Record tanpa file local:');
            foreach ($missing as $m) {
                $this->line("  - {$m}");
            }
        } elseif (! empty($missing)) {
            $this->warn(count($missing).' record tanpa file local (20 pertama):');
            foreach (array_slice($missing, 0, 20) as $m) {
                $this->line("  - {$m}");
            }
        }

        if ($dryRun) {
            $this->info('Dry-run selesai, tidak ada file yang diubah. Jalankan tanpa --dry-run untuk eksekusi.');
        }

        return $stats['failed'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
