<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pack_items', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('pack_id')
                ->constrained('pack_items')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('parent_id');
            $table->boolean('is_group')->default(false)->after('qty');
            $table->boolean('show_number')->default(true)->after('is_group');
        });

        // Backfill sort_order dari urutan id agar data lama tetap berurutan
        $packs = DB::table('packs')->pluck('id');
        foreach ($packs as $packId) {
            $ids = DB::table('pack_items')
                ->where('pack_id', $packId)
                ->orderBy('id')
                ->pluck('id');
            foreach ($ids as $i => $id) {
                DB::table('pack_items')->where('id', $id)->update(['sort_order' => $i]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('pack_items', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'sort_order', 'is_group', 'show_number']);
        });
    }
};
