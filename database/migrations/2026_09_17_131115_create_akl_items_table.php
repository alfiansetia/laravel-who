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
        Schema::create('akl_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('akl_id')->constrained('akls')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('code');
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akl_items');
    }
};
