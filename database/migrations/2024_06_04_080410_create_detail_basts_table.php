<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailBastsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_basts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bast_id');
            $table->unsignedBigInteger('product_id');
            $table->string('qty')->nullable();
            $table->string('lot')->nullable();
            $table->timestamps();
            $table->foreign('bast_id')->references('id')->on('basts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_basts');
    }
}
