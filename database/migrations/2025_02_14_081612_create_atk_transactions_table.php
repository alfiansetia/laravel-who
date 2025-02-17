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
        Schema::create('atk_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('atk_id');
            $table->date('date')->useCurrent();
            $table->string('pic')->default();
            $table->enum('type', ['in', 'out']);
            $table->unsignedBigInteger('qty')->default(0);
            $table->string('desc')->nullable();
            $table->timestamps();
            $table->foreign('atk_id')->references('id')->on('atks')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atk_transactions');
    }
};
