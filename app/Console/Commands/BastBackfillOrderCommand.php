<?php

namespace App\Console\Commands;

use App\Models\Bast;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('bast:backfill-order')]
#[Description('Isi kolom order DetailBast yang masih null (pengganti write-on-read di show).')]
class BastBackfillOrderCommand extends Command
{
    public function handle(): int
    {
        $basts = Bast::query()->with(['details' => fn ($q) => $q->orderBy('id')])->get();
        $fixed = 0;

        foreach ($basts as $bast) {
            foreach ($bast->details as $index => $detail) {
                if ($detail->order === null) {
                    $detail->update(['order' => $index]);
                    $fixed++;
                }
            }
        }

        $this->info("Backfill selesai: {$fixed} detail diperbaiki.");

        return self::SUCCESS;
    }
}
