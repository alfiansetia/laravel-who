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
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->date('date')->useCurrent();
            $table->string('number');
            $table->enum('type', ['dus', 'unit'])->default('unit');
            $table->enum('stock', ['stock', 'import'])->default('stock');
            $table->string('ri_po')->nullable();
            $table->enum('status', ['pending', 'done']);
            $table->date('email_on')->nullable();
            $table->string('pic')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};
