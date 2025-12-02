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
        Schema::create('alamat_barus', function (Blueprint $table) {
            $table->id();
            $table->string('tujuan');
            $table->text('alamat');
            $table->string('ekspedisi')->nullable();
            $table->integer('total_koli')->default(0);
            $table->string('up')->nullable();
            $table->string('tlp')->nullable();
            $table->string('do');
            $table->string('epur')->nullable();
            $table->string('untuk')->nullable();
            $table->text('note')->nullable();
            $table->text('note_wh')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat_barus');
    }
};
