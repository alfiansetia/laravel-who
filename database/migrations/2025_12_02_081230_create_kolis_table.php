<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kolis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alamat_baru_id')->constrained('alamat_barus')->onDelete('cascade');
            $table->string('urutan'); // can be "1", "1-7", "1,4,5,7"
            $table->string('nilai')->nullable();
            $table->enum('is_do', ['yes', 'no'])->default('no');
            $table->enum('is_pk', ['yes', 'no'])->default('no');
            $table->enum('is_asuransi', ['yes', 'no'])->default('no');
            $table->enum('is_banting', ['yes', 'no'])->default('no');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kolis');
    }
};
