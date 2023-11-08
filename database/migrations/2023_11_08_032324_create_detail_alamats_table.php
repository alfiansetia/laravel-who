<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailAlamatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_alamats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alamat_id');
            $table->unsignedBigInteger('product_id');
            $table->string('qty')->nullable();
            $table->string('lot')->nullable();
            $table->timestamps();
            $table->foreign('alamat_id')->references('id')->on('alamats')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_alamats');
    }
}
