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
        Schema::create('problem_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('problem_id');
            $table->date('date')->useCurrent();
            $table->text('desc')->nullable();
            $table->timestamps();
            $table->foreign('problem_id')->references('id')->on('problems')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_logs');
    }
};
