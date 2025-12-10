<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_estimate_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_estimate_id')->constrained('shipping_estimates')->cascadeOnDelete();
            $table->string('item_name');
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 15, 2)->default(0)->comment('Harga Total / Nilai Faktur');
            $table->timestamps();

            $table->index('shipping_estimate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_estimate_items');
    }
};
