<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_estimate_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_estimate_id')->constrained('shipping_estimates')->cascadeOnDelete();
            $table->string('shipper_name', 100)->comment('Nama Shipper: TIKI, TRC, JNE, dll');
            $table->enum('shipping_type', ['REG', 'DARAT'])->default('DARAT')->comment('REG=Udara (divisor 6000), DARAT=Laut (divisor 4000)');
            $table->decimal('rate_per_kg', 15, 2)->default(0)->comment('Tarif Ongkir per kg');
            $table->decimal('insurance_percentage', 5, 2)->default(0)->comment('Persentase Asuransi (contoh: 0.20 untuk 0.20%)');
            $table->decimal('packing_cost', 15, 2)->default(0)->comment('Biaya P. Kayu');
            $table->decimal('admin_fee', 15, 2)->default(0)->comment('Biaya Admin');
            $table->decimal('ppn_percentage', 5, 2)->default(0)->comment('Persentase PPN (contoh: 1 untuk 1%)');
            $table->string('estimated_days', 50)->nullable()->comment('Perkiraan Sampai (hari), contoh: 2-3');
            $table->timestamps();

            $table->index('shipping_estimate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_estimate_rates');
    }
};
