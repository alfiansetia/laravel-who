<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_estimate_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('shipping_estimate_packages')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('shipping_estimate_items')->cascadeOnDelete();
            $table->integer('quantity')->default(1)->comment('Jumlah item dalam koli ini');
            $table->timestamps();

            $table->unique(['package_id', 'item_id']);
            $table->index('package_id');
            $table->index('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_estimate_package_items');
    }
};
