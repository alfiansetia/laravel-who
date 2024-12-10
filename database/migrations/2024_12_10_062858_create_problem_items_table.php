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
        Schema::create('problem_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('problem_id');
            $table->integer('qty')->default(0);
            $table->string('lot')->nullable();
            $table->text('desc')->nullable();
            $table->timestamps();
            $table->foreign('problem_id')->references('id')->on('problems')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_items');
    }
};
