<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_estimate_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_estimate_id')->constrained('shipping_estimates')->cascadeOnDelete();
            $table->integer('package_number')->default(1);
            $table->string('package_name')->nullable();
            $table->integer('quantity')->default(1)->comment('Jumlah Koli');
            $table->decimal('weight_actual', 10, 2)->default(0)->comment('Berat Timbangan (kg)');
            $table->decimal('dimension_length', 10, 2)->default(0)->comment('Panjang (cm)');
            $table->decimal('dimension_width', 10, 2)->default(0)->comment('Lebar (cm)');
            $table->decimal('dimension_height', 10, 2)->default(0)->comment('Tinggi (cm)');
            $table->timestamps();

            $table->index('shipping_estimate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_estimate_packages');
    }
};
