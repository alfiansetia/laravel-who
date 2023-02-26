<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProblemdtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problemdts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('problem_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('snlot')->nullable();
            $table->string('qc')->nullable();
            $table->enum('status', ['ulang', 'import'])->default('ulang');
            $table->string('ri')->nullable();
            $table->string('desc')->nullable();
            $table->timestamps();
            $table->foreign('problem_id')->references('id')->on('problems')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('problemdts');
    }
}
